<?php

class CRTBR_Plugin {
	
	/**
	 * install
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function install() {

		update_option( 'crtbr_days_for_deletion', 365 );
		update_option( 'crtbr_hours_for_cron', 1 );
		update_option( 'crtbr_cron_enabled', false );
		update_option( 'crtbr_enable_logging', false );
		update_option( 'crtbr_cron_timeout', 30 );
		update_option( 'crtbr_save_timeout', 15 );
		update_option( 'crtbr_cron_maxrows', 50 );

	}

	/**
	 * uninstall
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function uninstall() {
		
		self::delete_plugin_files();
		delete_option( 'crtbr_days_for_deletion' );
		delete_option( 'crtbr_hours_for_cron' );
		delete_option( 'crtbr_cron_enabled' );
		delete_option( 'crtbr_enable_logging' );
		delete_option( 'crtbr_cron_timeout' );
		delete_option( 'crtbr_save_timeout' );
		delete_option( 'crtbr_cron_maxrows' );

	}

	/**
	 * delete_plugin_files
	 *
	 * Recursively delete folder of files for the plugin
	 *
	 * @param   array $links The links array.
	 * @return  array The links array.
	 */
	public static function delete_plugin_files() {
		$upload_dir              = wp_get_upload_dir();
		$path_checker_cache_path = $upload_dir['basedir'] . '/time-based-revisions/';
		// Check if the directory exists before proceeding
		if ( is_dir( $path_checker_cache_path ) ) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $path_checker_cache_path, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $fileinfo ) {
				$action = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
				$action( $fileinfo->getRealPath() );
			}
			// Remove the path_checker_cache directory itself
			rmdir( $path_checker_cache_path );
		}
	}
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		
		register_uninstall_hook( CRTBR_FILE, array( __CLASS__, 'uninstall' ) );
		register_deactivation_hook( CRTBR_FILE, array( __CLASS__, 'uninstall' ) );
		register_activation_hook( CRTBR_FILE, array( __CLASS__, 'install' ) );
		
		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . CRTBR_BASENAME . '/time-based-revisions.php', array( $this, 'add_settings_link' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_page' ) );
		}
		
	}
	
	/**
	 * add_settings_link
	 *
	 * Add settings link on plugin page
	 *
	 * @param   array $links The links array.
	 * @return  array The links array.
	 */
	public function add_settings_link( $links ) {
		$links[] = '<a href="' . $this->get_admin_url() . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}
	
	/**
	 * admin_init
	 *
	 * Register and enqueue admin stylesheet & scripts
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_init() {
		// only enqueue these things on the settings page
		if ( $this->get_current_admin_url() == $this->get_admin_url() ) {
			wp_register_style( 'crtbr_plugin_stylesheet', CRTBR_PLUGIN_DIR . 'admin/css/admin.css', [], CRTBR_VERSION );
			wp_enqueue_style( 'crtbr_plugin_stylesheet' );
			wp_register_script( 'crtbr_script', CRTBR_PLUGIN_DIR . 'admin/js/admin.js', array( 'jquery' ), CRTBR_VERSION, false );
			wp_localize_script( 'crtbr_script', 'crtbr_obj',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
			wp_enqueue_script( 'crtbr_script' );
		}
	}
	
	/**
	 * admin_page
	 *
	 * Register admin page and menu.
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Time-based Revisions', 'time-based-revisions' ),
			__( 'Time-based Revisions', 'time-based-revisions' ),
			'administrator',
			CRTBR_DIRNAME,
			array( $this, 'admin_page_settings' ),
			100
		);
	}
	
	/**
	 * admin_page_settings
	 *
	 * Render admin view
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_page_settings() {
		require_once CRTBR_DIRNAME . '/admin/view.php';
	}
	
	/**
	 * get_current_admin_url
	 *
	 * Get the current admin url.  Thanks WC!
	 *
	 * @param   void
	 * @return  void
	 */
	function get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
		if ( ! $uri ) {
			return '';
		}
		return remove_query_arg( array( '_wpnonce' ), admin_url( $uri ) );
	}
	
	/**
	 * get_admin_url
	 *
	 * Add settings link on plugin page
	 *
	 * @param   void
	 * @return  string the admin url
	 */
	public function get_admin_url() {
		return admin_url( 'options-general.php?page=' . CRTBR_BASENAME );
	}

}
