<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CRTBR_Cleaner {

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {

		// hook into existing revision filters
		add_filter( 'wp_revisions_to_keep', [ $this, 'set_revisions_to_max' ], CRTBR_ACTION_PRIORITY, 2 );
		add_filter( 'wp_save_post_revision_revisions_before_deletion', [ $this, 'save_cleanup' ], CRTBR_ACTION_PRIORITY, 2 );

		// setup CRON
		$hours_for_cron = crtbr()->options()->get( 'hours_for_cron' );
		$cron_enabled   = crtbr()->options()->get( 'cron_enabled' );
		if ( $cron_enabled ) {
			add_action( 'crtbr_cron_cleanup', [ $this, 'cron_cleanup' ] );
			if ( ! wp_next_scheduled( 'crtbr_cron_cleanup' ) ) {
				wp_schedule_single_event( time() + ( $hours_for_cron * 60 * 60 ), 'crtbr_cron_cleanup' );
			}
		}

	}

	/**
	 * set_revisions_to_max
	 *
	 * Override the existing revisions number unless the post type doesn't support revisions.
	 *
	 * @param   string $num The revision number from wp-config.
	 * @param   string $post The incoming post from the page save event.
	 * @return  void
	 */
	function set_revisions_to_max ( $num, $post ) {
		$num = PHP_INT_MAX;
		if ( ! post_type_supports( $post->post_type, 'revisions' ) ) {
			$num = 0;
		}
		return $num;
	}

	/**
	 * cron_cleanup
	 *
	 * The function to run the cleanup via CRON.  We're doing maxrows, because on a site with
	 * thousands of posts, it's time-intensive.  You can't really get through more than 
	 * one post per second on deletion.  But the settings are available if you have a more performant
	 * server.  We're setting the default timeout to 30s for CRON since it should be more performant 
	 * than the page save event.
	 *
	 * @return  void
	 */
	function cron_cleanup () {
		$cron_timeout      = crtbr()->options()->get( 'cron_timeout' );
		$cron_enabled      = crtbr()->options()->get( 'cron_enabled' );
		$cron_maxrows      = crtbr()->options()->get( 'cron_maxrows' );
		$revisions_to_skip = crtbr()->options()->get( 'revisions_to_skip' );
		if ( $cron_enabled ) {
			crtbr()->log( "CRON Cleanup Started" );
			$args = [
				'order'       => 'ASC',
				'orderby'     => 'date ID',
				'post_type'   => 'revision',
				'post_status' => 'inherit',
				'numberposts' => $cron_maxrows,
				'exclude'     => $revisions_to_skip,
			];
			$revisions = get_posts( $args );
			crtbr()->log( "Found Posts:" . count( $revisions ) );
			crtbr()->cleaner()->delete_revisions( $revisions, $cron_timeout );
		}
	}

	/**
	 * save_cleanup
	 *
	 * Perform the on-save cleanup hook from WP's revision function.
	 *
	 * @param   array $revisions The revisions array coming from the original revision event. See wp-includes/revision.php "wp_save_post_revision_revisions_before_deletion"
	 * @param   array $postid The ID of the post that was saved
	 * @return  array the remainining posts that were newer than the retention window
	 */
	function save_cleanup ( $revisions, $postid ) {
		if ( crtbr()->options()->get( 'cron_enabled' ) && crtbr()->options()->get( 'disable_save_clean' ) ) {
			crtbr()->log( "Skipping Cleanup for post ID " . $postid );
			return [];
		}
		crtbr()->log( "Save Cleanup Started for post ID " . $postid );
		$save_timeout = crtbr()->options()->get( 'save_timeout' );
		return crtbr()->cleaner()->delete_revisions( $revisions, $save_timeout );
	}

	/**
	 * delete_revisions
	 *
	 * The main function for deleting old revisions.  Finds revisions X days old, then deletes them.
	 *
	 * @param   array $revisions The revisions array of posts to delete.
	 * @param   array $limit The maximum time for this function to run
	 * @return  array Returns any posts that are younger than the deletion window.  This will get passed back to the normal revision cycle and wordpress will do it's normal stuff.
	 */
	function delete_revisions ( $revisions, $limit=15 ) {
		$days_for_deletion = crtbr()->options()->get( 'days_for_deletion' );
		$count             = 0;
		$delete            = [];
		$keep              = [];
		$skip              = [];
		$start             = microtime( true );
		// loop through each revision to determine if we're going to delete it, or keep it
		foreach( $revisions as $revision ) {
			$do_delete = strtotime( $revision->post_modified ) < strtotime( "-$days_for_deletion days" ) && strtotime( $revision->post_date ) < strtotime( "-$days_for_deletion days"  );
			// if we're going to delete it, use a filter to see if we are going to override it
			if ( $do_delete ) {
				$do_delete = apply_filters( 'crtbr_delete_revision', $do_delete, $revision );
				// if we're overriding the delete, let's add it to the skip array so we don't query it in the future
				if ( ! $do_delete ) {
					$skip[] = $revision;
				}
			}
			if ( $do_delete ) {
				$delete[] = $revision;
			} else {
				$keep[] = $revision;
			}
		}
		// if we have posts to skip, save the ID's so we don't query them in the future via the CRON
		if ( count( $skip ) > 0 ) {
			$revisions_to_skip = crtbr()->options()->get( 'revisions_to_skip' );
			for ( $i=0; $i < count( $skip ); $i++ ) {
				if ( ! in_array( $skip[ $i ]->ID, $revisions_to_skip ) ) {
					crtbr()->log( "Saving " . $skip[ $i ]->ID . " to Revsision skip array" );
					$revisions_to_skip[] = $skip[ $i ]->ID;
				}
			}
			$revisions_to_skip = array_unique( $revisions_to_skip );
			crtbr()->options()->set( 'revisions_to_skip', $revisions_to_skip );
		}
		// ok, run though as many deletes as possible before we hit our time limit
		for ( $i = 0; $i < count( $delete ); $i++ ) {
			crtbr()->log( "Deleting Revision " . $delete[ $i ]->ID );
			wp_delete_post_revision( $delete[ $i ]->ID );
			crtbr()->cleaner()->update_stats();
			$count += 1;
			$elapsed = microtime( true ) - $start;
			if ( $elapsed >= $limit ) {
				crtbr()->log( "Deleting stopped at " . $elapsed );
				break;
			}
		}
		crtbr()->log( "Total deleted this run: " . $count );
		return $keep;
	}

	/**
	 * update_stats
	 *
	 * The function to log delete events.  Only called on a successful revision delete.
	 *
	 * @param   void
	 * @return  void
	 */
	function update_stats () {
		$crtbr_stats = crtbr()->options()->get( 'stats' );
		$date_key = gmdate( "Y-m-d-H" );
		if ( ! isset( $crtbr_stats[$date_key] ) ) {
			$crtbr_stats[$date_key] = 0;
		}
		$crtbr_stats[$date_key] += 1;
		crtbr()->options()->set( 'stats', $crtbr_stats );
	}

}
