<?php

class qxScalar extends qxDataType {
	
	public $_value;
	
	function __construct( $value ) {
		parent::__construct();
		
		$this->_value = $value;
	}
		
	public function valueOf() {
		return $this->_value;
	}
	
}

?>