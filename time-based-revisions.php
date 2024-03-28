<?php
/**
 * Plugin Name:  Time-based Revisions
 * Plugin URI:   https://compiledrogue.com/
 * Description:  Maximize WordPress efficiency with Time-based Revision Cleanup: manage post histories by age, not count, with optional auto-cleanups.
 * Version:      1.0.2
 * Author:       Compiled Rogue
 * Author URI:   https://compiledrogue.com
 * License:      GPL2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  time-based-revisions
 *
 * @package     TimeBasedRevisions
 * @author      Compiled Rogue
 * @copyright   Copyright (c) 2024, Compiled Rogue LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__ . '/classes/crtbr-plugin.php';
require_once __DIR__ . '/classes/crtbr-admin-panel.php';
require_once __DIR__ . '/classes/crtbr-options.php';
require_once __DIR__ . '/classes/crtbr-logs.php';

if ( ! class_exists( 'TimeBasedRevisions' ) ) :

	class TimeBasedRevisions {
		
		/** @var string The plugin version number. */
		var $version = '1.0.2';
		
		/** @var string Shortcuts. */
		var $plugin;
		var $options;
		var $logs;
		
		/**
		 * __construct
		 *
		 * A dummy constructor to ensure TimeBasedRevisions is only setup once.
		 * 
		 * @param   void
		 * @return  void
		 */
		function __construct() {
			// Do nothing.
		}
		
		/**
		 * initialize
		 *
		 * Sets up the TimeBasedRevisions plugin.
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {

			// Define constants.
			$this->define( 'CRTBR', true );
			$this->define( 'CRTBR_DEBUG', false );
			$this->define( 'CRTBR_ACTION_PRIORITY', 99999 );
			$this->define( 'CRTBR_FILE', __FILE__ );
			$this->define( 'CRTBR_DIRNAME', dirname( __FILE__ ) );
			$this->define( 'CRTBR_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
			$this->define( 'CRTBR_BASENAME', basename( dirname( __FILE__ ) ) );
			$this->define( 'CRTBR_VERSION', $this->version );
			
			// Do all the plugin stuff.
			$this->plugin    = new CRTBR_Plugin();
			$this->options   = new CRTBR_Options();
			$this->logs      = new CRTBR_Logs();

			if ( is_admin() ) {
				// load up our admin classes
				$admin = new CRTBR_AdminPanel();
			} else {
				// no front-end code
			}

			// hook into existing revision filters
			add_filter( 'wp_revisions_to_keep', array( $this, 'set_revisions_to_max' ), CRTBR_ACTION_PRIORITY, 2 );
			add_filter( 'wp_save_post_revision_revisions_before_deletion', array( $this, 'save_cleanup' ), CRTBR_ACTION_PRIORITY, 2 );

			// setup CRON
			$hours_for_cron = crtbr()->options()->get( 'hours_for_cron', 1 );
			$cron_enabled   = crtbr()->options()->get( 'cron_enabled' );
			if ( $cron_enabled ) {
				add_action( 'crtbr_cron_cleanup', array( $this, 'cron_cleanup' ) );
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
			$cron_timeout = crtbr()->options()->get( 'cron_timeout', 30 );
			$cron_enabled = crtbr()->options()->get( 'cron_enabled' );
			$cron_maxrows = crtbr()->options()->get( 'cron_maxrows', 50 );
			if ( $cron_enabled ) {
				crtbr()->log( "CRON Cleanup Started" );
				$args = array(
					'order'       => 'ASC',
					'orderby'     => 'date ID',
					'post_type'   => 'revision',
					'post_status' => 'inherit',
					'numberposts' => $cron_maxrows,
				);
				$revisions = get_posts( $args );
				crtbr()->log( "Found Posts:" . count( $revisions ) );
				crtbr()->delete_revisions( $revisions, $cron_timeout );
			}
		}

		/**
		 * save_cleanup
		 *
		 * Perform the on-save cleanup hook from WP's revision function.
		 *
		 * @param   array $revisions The revisions array coming from the original revision event. See wp-includes/revision.php "wp_save_post_revision_revisions_before_deletion"
		 * @return  array
		 */
		function save_cleanup ( $revisions, $postid ) {
			crtbr()->log( "Save Cleanup Started for post ID " . $postid );
			$save_timeout = crtbr()->options()->get( 'save_timeout', 15 );
			return crtbr()->delete_revisions( $revisions, $save_timeout );
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
			$days_for_deletion = crtbr()->options()->get( 'days_for_deletion', 365 );
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
		 * __call
		 *
		 * Sugar function to access class properties
		 *
		 * @param   string $name The property name.
		 * @return  void
		 */
		public function __call( $name, $arguments ) {
			return $this->{$name};
		}
		
		/**
		 * define
		 *
		 * Defines a constant if doesnt already exist.
		 *
		 * @param   string $name The constant name.
		 * @param   mixed  $value The constant value.
		 * @return  void
		 */
		function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		/**
		 * log
		 *
		 * Output logging to the debug.
		 *
		 * @param   mixed  $log The value.
		 * @return  void
		 */
		function log( $log ) {
			crtbr()->logs()->log( $log );
		}
		
	}

	/*
	* crtbr
	*
	* The main function responsible for returning the one true TimeBasedRevisions Instance to functions everywhere.
	* Use this function like you would a global variable, except without needing to declare the global.
	*
	* @param   void
	* @return  TimeBasedRevisions
	*/
	function crtbr() {
		global $crtbr;
		// Instantiate only once.
		if ( ! isset( $crtbr ) ) {
			$crtbr = new TimeBasedRevisions();
			$crtbr->initialize();
		}
		return $crtbr;
	}

	// Instantiate.
	crtbr();

endif; // class_exists check

