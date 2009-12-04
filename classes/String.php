<?php

class String extends Scalar {
	
	function __construct( $value ) {
		parent::__construct($value);
		
		//$this->_value = (string) $this->_value
		
		if ( $this->_value !== null )
			settype($this->_value, 'string');
	}
	
	/**
	 * 
	 * @return bool true if value not empty 
	 */
	public function required() {
		return ( isset($this->_value) && strlen($this->_value) > 0 );
	}
	
	/**
	 * 
	 * @return bool true if length of value >= $length
	 * @param int $length
	 */
	public function min($length = 0) {
		return ( strlen($this->_value) >= (int) $length );
	}
	
	/**
	 * 
	 * @param string $value if value is object then getting valueOf()
	 * @return bool true if $value equals this object value 
	 */
	public function eq($value = '') {
		return ( $this->_value == ( is_object($value) ? $value->valueOf() : (string) $value ) );
	}
	
	/**
	 * 
	 * @param string $value if value is object then getting valueOf()
	 * @return bool true if $value not equals this object value
	 */
	public function neq($value ='') {
		return ( $this->_value != ( is_object($value) ? $value->valueOf() : (string) $value ) );
	}
	
}

?>