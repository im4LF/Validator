<?php

/**
 * 
 */
class ClassConfig {
	
	protected $_config = array();
	private static $__instance = null;
	
	public static function getInstance() {
		if ( !self::$__instance ) {
			$class = __CLASS__; 
			self::$__instance = new $class;
		}
			
		return self::$__instance;
	}
	
	/**
	 * Load configuration for class from docComment and return it.
	 * 
	 * @param object or string $class - class name or object of class
	 * @return array or false - array if configuration successfully loaded else false
	 */
	public function load($class) {
		$reflection = null;
		if ( is_object($class) ) {
			$class = get_class($class);
		}
		
		if ( array_key_exists($class, $this->_config) ) return $this->_config[$class]['config'];
		
		$reflection = new ReflectionClass($class);
		
		if ( !$reflection )
			return false;
		
		$this->_config[$class]['reflection'] = $reflection;
		
		$this->getParentConfig($class, $reflection);	// recursively load all parent classes configurations
		$this->_parseClass($class);						// parse configuration of this class
		
		return $this->_config[$class]['config'];
	}
	
	/**
	 * Recursively load parents for class.
	 * If configuration for parent class defined then save class as parent 
	 * 
	 * @param string $class
	 * @param ReflectionClass $reflection
	 * @return 
	 */
	protected function getParentConfig($class, $reflection) {
		$parent_class = $reflection->getParentClass();	// get parent class
		
		if ( $parent_class ) {
			// if configuration defined then save parent class name			
			//if ( isset($this->_config[$parent_class->getName()]['config']) )
			$this->_config[$class]['parents'][] = $parent_class->getName();
				
			$this->getParentConfig($class, $parent_class);	// get parent for parent
		}
	}
	
	protected function _parseClass($class_name) {
		$doc_comment = $this->_config[$class_name]['reflection']->getDocComment();

		$config = $this->_parseProperties($doc_comment);
		if ( ($buf = $this->_parseActions($doc_comment)) )
			$config['actions'] = $buf;
		
		// merge configuration of parent classes with this configuration
		if ( isset($this->_config[$class_name]['parents']) ) {
			for ( $i=sizeof($this->_config[$class_name]['parents'])-1; $i>=0; $i-- ) {
				$parent_class = $this->_config[$class_name]['parents'][$i];
				
				// no configuration
				if ( !array_key_exists('config', $this->_config[$parent_class]) ) continue;
				
				$parent_config = $this->_config[$parent_class]['config'];
				$config = array_merge_recursive_distinct_test($parent_config, $config);
			}
		}
		
		if (!count($config)) return;
		
		$this->_config[$class_name]['config'] = $config;
	}
	
	protected function _parseProperties($doc_comment) {
		$matches = array();
		$config = array();
		
		// get properties
		preg_match_all('/@var\s+(\w+)(\s+(\w+))?(\s+\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER);
		
		// if count of properties more then one - its object
		if ( count($matches) > 1 ) {
			foreach ( $matches as $match ) {
				$config['properties'][$match[3]] = array(
					'type' => $match[1]
				);
				
				if ( !isset($match[5]) ) continue;	// no rules
				
				$config['properties'][$match[3]]['rules'] = $this->_parseString($match[5]);
			}
		}
		// else its simple type
		elseif ( count($matches) == 1 ) {
			$config = array(
				'type' => $matches[0][1]
			);
			
			if ( isset($matches[0][5]) )				
				$config['rules'] = $this->_parseString($matches[0][5]);
		}
		
		// if defined rules
		if ( ($buf = $this->_parseRules($doc_comment)) ) {
			$config['rules'] = array_merge((array)$config['rules'], $buf);
		}
		
		return $config;
	}
	
	protected function _parseRules($doc_comment) {
		if ( !preg_match_all('/@rules\s+\[(.*?)\]/', $doc_comment, $matches, PREG_SET_ORDER) ) return false;
			
		$rules = array();
		foreach ( $matches as $match ) {
			$rules = array_merge($rules, $this->_parseString($match[1]));
		}
		
		return $rules;
	}
	
	protected function _parseActions($doc_comment) {
		$matches = array();
		$actions = array();
		if ( !preg_match_all('/@action\s+([\w\:\/\*]+)\s+(\w+)(\s+\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER) ) return $actions;
		
		foreach ( $matches as $match ) {
			$actions[$match[1]] = array(
				'action' => $match[1],
				'method' => $match[2],
				'params' => $this->_parseString($match[4])
			);
		}
		
		return $actions;
	}
	
	protected function _parseString($string) {
		$res = array();
		if ( !preg_match_all('/(.+?)(,\s+|$)/', $string, $rules, PREG_SET_ORDER) ) return $res;
		
		foreach ( $rules as $rule ) {
			$res[] = $rule[1];
		}
		
		return $res;
	}
}
?>