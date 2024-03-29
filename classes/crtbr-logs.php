<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CRTBR_Logs {

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
	}

	/**
	 * clear
	 *
	 * Output logging to the debug.
	 *
	 * @return  array
	*/
	function clear() {
		$upload_info = wp_get_upload_dir();
		$data_dir    = $upload_info['basedir'] . "/time-based-revisions/";
		file_put_contents( $data_dir . "log.txt", '' );
		return [ "index" => 0, "count" => 20, "max" => 0, 'rows' => [] ];
	}

	/**
	 * clear
	 *
	 * Output logging to the debug.
	 *
	 * @return  void
	*/
	function delete() {
		$upload_info = wp_get_upload_dir();
		$log_file    = $upload_info['basedir'] . "/time-based-revisions/log.txt";
		if ( file_exists( $log_file ) ) {
			unlink( $log_file );
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
		if ( crtbr()->options()->get( 'enable_logging' ) ) {
			$upload_info = wp_get_upload_dir();
			$data_dir    = $upload_info['basedir'] . "/time-based-revisions/";
			wp_mkdir_p( $data_dir );
			file_put_contents( $data_dir . "log.txt", time() . "," . $log . "\n", FILE_APPEND );
		}
	}

	/**
	 * get_blocked
	 *
	 * Get subset of blocked logs
	 *
	 * @param   string $name The meta name.
	 * @return  array
	 */
	public function get_logs( $page=0 ) {
		$upload_info = wp_get_upload_dir();
		$rows        = [];
		$logfile     = $upload_info['basedir'] . "/time-based-revisions/log.txt";
		$row_count   = 20;
		$start       = $page * $row_count;
		$file        = new SplFileObject( $logfile );
		$file->seek( $start );
		for ( $i=0; $i < $row_count; $i++ ) {
			$line = $file->current();
			if ( $line ) {
				$data = explode( ',', $line );
				$rows[] = [ $data[0], $data[1] ];
				$file->next();
			}
		}
		$file->seek( PHP_INT_MAX );
		$max = $file->key();
		return [ "index" => $start, "count" => $row_count, "max" => $max, 'rows' => $rows ];
	}

}
