<?php
/**
 * Plugin Name:  Time-based Revisions
 * Plugin URI:   https://compiledrogue.com/
 * Description:  Maximize WordPress efficiency with Time-based Revision Cleanup: manage post histories by age, not count, with optional auto-cleanups.
 * Version:      1.0.10
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
require_once __DIR__ . '/classes/crtbr-cleaner.php';

if ( ! class_exists( 'TimeBasedRevisions' ) ) :

	class TimeBasedRevisions {
		
		/** @var string The plugin version number. */
		var $version = '1.0.10';
		
		/** @var string Shortcuts. */
		var $plugin;
		var $options;
		var $cleaner;
		
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
			$this->options   = new CRTBR_Options();
			$this->plugin    = new CRTBR_Plugin();
			$this->cleaner   = new CRTBR_Cleaner();

			if ( is_admin() ) {
				// load up our admin classes
				$admin = new CRTBR_AdminPanel();
			} else {
				// no front-end specific code
			}
			
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
			if ( is_array( $log ) || is_object( $log ) ) {
				$log = print_r( $log, true );
			}
			if ( defined( 'CRTBR_DEBUG' ) && CRTBR_DEBUG && WP_DEBUG ) {
				error_log( $log );
			}
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
