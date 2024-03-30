<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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

		crtbr()->options()->save_defaults();

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
		
		crtbr()->options()->delete_defaults();
		crtbr()->logs()->delete();
		wp_clear_scheduled_hook( 'crtbr_cron_cleanup' );

	}
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		
		register_uninstall_hook( CRTBR_FILE, [ __CLASS__, 'uninstall' ] );
		register_deactivation_hook( CRTBR_FILE, [ __CLASS__, 'uninstall' ] );
		register_activation_hook( CRTBR_FILE, [ __CLASS__, 'install' ] );
		
		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . CRTBR_BASENAME . '/time-based-revisions.php', [ $this, 'add_settings_link' ] );
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'admin_page' ] );
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
			wp_register_script( 'crtbr_script', CRTBR_PLUGIN_DIR . 'admin/js/admin.js',[ 'jquery' ], CRTBR_VERSION, false );
			wp_localize_script( 'crtbr_script', 'crtbr_obj', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
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
			[ $this, 'admin_page_settings' ],
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
		return remove_query_arg( [ '_wpnonce' ], admin_url( $uri ) );
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
