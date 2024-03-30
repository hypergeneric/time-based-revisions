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
		$cron_timeout = crtbr()->options()->get( 'cron_timeout' );
		$cron_enabled = crtbr()->options()->get( 'cron_enabled' );
		$cron_maxrows = crtbr()->options()->get( 'cron_maxrows' );
		if ( $cron_enabled ) {
			crtbr()->log( "CRON Cleanup Started" );
			$args = [
				'order'       => 'ASC',
				'orderby'     => 'date ID',
				'post_type'   => 'revision',
				'post_status' => 'inherit',
				'numberposts' => $cron_maxrows,
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
		$start             = microtime( true );
		foreach( $revisions as $revision ) {
			$do_delete = strtotime( $revision->post_modified ) < strtotime( "-$days_for_deletion days" ) && strtotime( $revision->post_date ) < strtotime( "-$days_for_deletion days"  );
			$do_delete = apply_filters( 'crtbr_delete_revision', $do_delete, $revision );
			if ( $do_delete ) {
				$delete[] = $revision;
			} else {
				$keep[] = $revision;
			}
		}
		for ( $i = 0; isset( $delete[ $i ] ); $i++ ) {
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
