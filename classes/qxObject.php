<?php

class qxObject extends qxDataType {
	
	protected $_properties;
	
	function __construct( $value ) {
		parent::__construct();
		
		$config = ClassConfig::getInstance()->load($this->_class);	// load configuration of this class				
		
		// throughout fields of this class
		foreach ($config['properties'] as $field => $params) {
			$this->{$field} = new $params['type']($value[$field]);	// initialize field
		}
	}
	
	function __set($name, $value) {
		$this->_properties[$name] = $value;
	}
	
	function __get($name) {
		return $this->_properties[$name];
	}
}
?>