<?php

class DataType {
	
	protected $_class;
	
	public function __construct() {
		$this->_class = get_class($this);
	}
	
}

?>