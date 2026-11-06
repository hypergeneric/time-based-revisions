<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CRTBR_AdminPanel {
	
	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_crtbr_save_admin_page', [ $this, 'save_admin_page' ] );
			add_action( 'wp_ajax_crtbr_logs_get_page', [ $this, 'logs_get_page' ] );
			add_action( 'wp_ajax_crtbr_logs_clear', [ $this, 'logs_clear' ] );
			add_action( 'wp_ajax_crtbr_get_stats_data', [ $this, 'get_stats_data' ] );
		}
	}

	/**
	 * get_stats_data
	 *
	 * Get the stats data to power the graph
	 *
	 * @param   void
	 * @return  void
	 */
	public function get_stats_data() {
		$this->authorize_request();
		check_ajax_referer( 'crtbr_admin', 'nonce' );

		// set vars based on requested format
		$timespan = isset( $_POST['timespan'] ) && is_string( $_POST['timespan'] ) ? sanitize_key( wp_unslash( $_POST['timespan'] ) ) : '';
		if ( ! in_array( $timespan, [ 'last-24h', 'last-30d', 'last-60d', 'last-1y' ], true ) ) {
			wp_send_json_error( 'Invalid timespan.', 400 );
		}
		switch ( $timespan ) {
			case 'last-24h':
				$date_format       = 'Y-m-d-H';
				$interval_period   = '1 hour';
				$date_start_period = '-24 hours';
				$date_end_period   = '+1 hour';
				break;
			case 'last-30d':
				$date_format       = 'Y-m-d';
				$interval_period   = '1 day';
				$date_start_period = '-30 day';
				$date_end_period   = '+1 day';
				break;
			case 'last-60d':
				$date_format       = 'Y-m-d';
				$interval_period   = '1 day';
				$date_start_period = '-60 day';
				$date_end_period   = '+1 day';
				break;
			case 'last-1y':
				$date_format       = 'Y-m-d';
				$interval_period   = '1 day';
				$date_start_period = 'first day of January this year';
				$date_end_period   = '+1 day';
				break;
		}
		$stats      = crtbr()->options()->get( 'stats' );
		$date_array = [];
		$chart_data = [];
		$date_start = new DateTime();
		$date_end   = new DateTime();
		$interval   = DateInterval::createFromDateString( $interval_period );
		
		// create our data group
		foreach ( $stats as $key => $value ) {
			$date_key = $key;
			if ( $interval_period != '1 hour' ) {
				$date_key = explode( '-', $key );
				array_pop( $date_key );
				$date_key = implode( '-', $date_key );
			}
			if ( ! isset( $date_array[$date_key] ) ) {
				$date_array[$date_key] = 0;
			}
			$date_array[$date_key] += $value;
		}

		// create our chart data
		$date_start->modify( $date_start_period );
		$date_end->modify( $date_end_period );
		foreach ( new DatePeriod( $date_start, $interval, $date_end ) as $dt ) {
			$key   = $dt->format( $date_format );
			$value = 0;
			if ( isset( $date_array[$key] ) ) {
				$value = $date_array[$key];
			}
			$chart_data[$key] = $value;
		}

		// send response
		wp_send_json_success( $chart_data );
		
	}

	/**
	 * logs_clear
	 *
	 * clear the log file.
	 *
	 * @param   void
	 * @return  void
	 */
	public function logs_clear() {
		$this->authorize_request();
		check_ajax_referer( 'crtbr_admin', 'nonce' );

		wp_send_json_success( crtbr()->logs()->clear() );
		
	}

	/**
	 * logs_get_next
	 *
	 * get the next page of blocked logs.
	 *
	 * @param   void
	 * @return  void
	 */
	public function logs_get_page() {
		$this->authorize_request();
		check_ajax_referer( 'crtbr_admin', 'nonce' );

		$page = isset( $_POST['page'] ) && is_scalar( $_POST['page'] ) ? max( 0, intval( wp_unslash( $_POST['page'] ) ) ) : 0;
		$rows = crtbr()->logs()->get_logs( $page );
		
		wp_send_json_success( $rows );
		
	}
	
	/**
	 * save_admin_page
	 *
	 * Update Form Data when submitted
	 *
	 * @param   void
	 * @return  void
	 */
	private function authorize_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.', 403 );
		}
	}

	public function save_admin_page() {
		$this->authorize_request();
		check_ajax_referer( 'crtbr_admin', 'nonce' );
		$values = [];
		foreach ( [ 'days_for_deletion', 'hours_for_cron', 'cron_timeout', 'save_timeout', 'cron_maxrows' ] as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$value = filter_var( wp_unslash( $_POST[ $key ] ), FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] );
			if ( false === $value ) {
				wp_send_json_error( 'Settings must be positive whole numbers.', 400 );
			}
			$values[ $key ] = $value;
		}
		foreach ( [ 'cron_enabled', 'enable_logging', 'disable_save_clean' ] as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = filter_var( wp_unslash( $_POST[ $key ] ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				if ( null === $value ) {
					wp_send_json_error( 'Invalid checkbox value.', 400 );
				}
				$values[ $key ] = $value;
			}
		}
		foreach ( $values as $key => $value ) {
			crtbr()->options()->set( $key, $value );
		}
		if ( isset( $values['cron_enabled'] ) || isset( $values['hours_for_cron'] ) ) {
			wp_clear_scheduled_hook( 'crtbr_cron_cleanup' );
			if ( crtbr()->options()->get( 'cron_enabled' ) ) {
				wp_schedule_single_event( time() + max( 1, (int) crtbr()->options()->get( 'hours_for_cron' ) ) * HOUR_IN_SECONDS, 'crtbr_cron_cleanup' );
			}
		}
		wp_send_json_success();
	}

}
