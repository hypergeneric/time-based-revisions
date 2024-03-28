<?php

class CRTBR_Options {
	
	/** @var string Local array to store lookups. */
	var $lookup = [];

	/**
	 * __construct
	 * 
	 * @param   void
	 * @return  void
	 */
	public function __construct() {
	}
	
	/**
	 * get
	 *
	 * Sugar function to save options meta.
	 *
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function get( $name, $default=null ) {
		if ( isset( $this->lookup[$name] ) ) {
			return $this->lookup[$name];
		}
		$value = get_option( 'crtbr_' . $name );
		return $value ? $value : $default;
	}
	
	/**
	 * set
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   string $name The meta name.
	 * @param   mixed $value The meta value.
	 * @param   boolean $autoload Autoload setting.
	 * @return  void
	 */
	public function set( $name, $value, $autoload=true ) {
		if ( isset( $this->lookup[$name] ) ) {
			unset( $this->lookup[$name] );
		}
		update_option( 'crtbr_' . $name, $value, $autoload );
	}

}
