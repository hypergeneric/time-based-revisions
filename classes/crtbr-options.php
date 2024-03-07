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
	 * getpostmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @return  mixed The meta value
	 */
	public function getpostmeta( $id, $name, $default=null ) {
		if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				return $this->lookup[$id][$name];
			}
		}
		$value = get_post_meta( $id, 'crtbr_' . $name, true );
		return $value ? $value : $default;
	}
	
	/**
	 * setpostmeta
	 *
	 * Sugar function to save post meta.
	 *
	 * @param   int $id The post id.
	 * @param   string $name The meta name.
	 * @param   mixed $value The meta value.
	 * @return  void
	 */
	public function setpostmeta( $id, $name, $value ) {
		if ( isset( $this->lookup[$id] ) ) {
			if ( isset( $this->lookup[$id][$name] ) ) {
				unset( $this->lookup[$id][$name] );
			}
		}
		update_post_meta( $id, 'crtbr_' . $name, $value );
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
