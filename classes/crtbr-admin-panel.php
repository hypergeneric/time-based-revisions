<?php

class CRTBR_AdminPanel {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_crtbr_save_admin_page', array( $this, 'save_admin_page' ) );
		}
	}
	
	/**
	 * save_admin_page
	 *
	 * Update Form Data when submitted
	 *
	 * @param   void
	 * @return  void
	 */
	public function save_admin_page() {
		
		$literals = [ 'days_for_deletion', 'hours_for_cron', 'cron_timeout', 'save_timeout', 'cron_maxrows' ];
		$bools = [ 'cron_enabled' ];
		
		$post_clean = filter_input_array( INPUT_POST, [
			'hours_for_cron'    => FILTER_SANITIZE_NUMBER_INT,
			'days_for_deletion' => FILTER_SANITIZE_NUMBER_INT,
			'cron_timeout'      => FILTER_SANITIZE_NUMBER_INT,
			'cron_maxrows'      => FILTER_SANITIZE_NUMBER_INT,
			'save_timeout'      => FILTER_SANITIZE_NUMBER_INT,
			'cron_enabled'      => FILTER_VALIDATE_BOOLEAN,
		] );
		
		foreach ( $literals as $key ) {
			if ( isset( $post_clean[ $key ] ) ) {
				crtbr()->options()->set( $key, $post_clean[ $key ] );
			}
		}
		
		foreach ( $bools as $key ) {
			if ( isset( $post_clean[ $key ] ) ) {
				crtbr()->options()->set( $key, $post_clean[ $key ] == 'true' );
			}
		}
		
	}

}
