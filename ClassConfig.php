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
		//$this->parseConfig($class);						// parse configuration of this class
		
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
			if ( isset($this->_config[$parent_class->getName()]['config']) )			
				$this->_config[$class]['parents'][] = $parent_class->getName();
				
			$this->getParentConfig($class, $parent_class);	// get parent for parent
		}
	}
	
	/**
	 * Parse configuration from docComment.
	 * 
	 * @param string $class
	 * @return boolean false if configuration not defined
	 */
	protected function parseConfig($class) {
		$doc_comment = $this->_config[$class]['reflection']->getDocComment();
		preg_match_all("/<config(\s+?type\s*?\=\s*\"(.*)?\")?>(.*)<\/config>/is", $doc_comment, $out, PREG_SET_ORDER);
		
		if ( empty($out) )
			return false;
		
		$config = array (
			'type' => $out[0][2],
			'string' => $out[0][3],
			'config' => null
		); 
	
		// parse configuration		
		if ( $config['type'] == 'json' ) {
			$config['string'] = preg_replace("/^\s+\*\s/im", '', $config['string']);
			$config['string'] = preg_replace("/([a-zA-Z0-9_\-]+?):/" , '"$1":', $config['string']);
			$config['config'] = json_decode($config['string'], true);
		}
		else {
			$config['string'] = preg_replace("/^\s+\*\s/im", '', $config['string']);
			$config['config'] = Spyc::YAMLLoad($config['string']);
		}
		
		// merge configuration of parent classes with this configuration
		if ( isset($this->_config[$class]['parents']) ) {
			for ( $i=sizeof($this->_config[$class]['parents'])-1; $i>=0; $i-- ) {
				$parent_class = $this->_config[$class]['parents'][$i];
				
				// higher priority in the current configuration
				$parent_class_config = $this->_config[$parent_class]['config'];
				$config['config'][$class] = array_merge_recursive_distinct_test($parent_class_config, $config['config'][$class]);
			}
		}
		
		$this->_config[$class]['config'] = $config['config'][$class];
		return true;
	}
	
	protected function _parseClass($class_name) {
		$doc_comment = $this->_config[$class_name]['reflection']->getDocComment();
		/*if ( ($buf = $this->_parseProperties($doc_comment)) )
			$config = $buf;*/
		//$config['actions'] = $this->_parseActions($doc_comment);
		
		$matches = array();
		if ( !preg_match_all('/@var\s+(\w+)(\s+(\w+))?(\s+\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER) ) return;
		
		$config = array();
		
		if ( count($matches) > 1 ) {	// its object
			foreach ( $matches as $match ) {
				$config['properties'][$match[3]] = array(
					'type' => $match[1]
				);
				
				if ( !isset($match[5]) ) continue;	// no rules
				
				$config['properties'][$match[3]]['rules'] = $this->_parseRules($match[5]);
			}
			if ( !preg_match_all('/@rules\s+\[(.*?)\]/', $doc_comment, $matches, PREG_SET_ORDER) ) return;
			
			foreach ( $matches as $rules ) {
				$config['rules'] = array_merge((array)$config['rules'], $this->_parseRules($rules[1]));
			}
		}
		else {
			$this->_config[$class_name]['config'] = array(
				'type' => $match[1]
			);
			
			if ( !isset($match[5]) ) return;	// no rules
				
			$config['rules'] = $this->_parseRules($match[5]);
		}
		
		// merge configuration of parent classes with this configuration
		if ( isset($this->_config[$class_name]['parents']) ) {
			for ( $i=sizeof($this->_config[$class_name]['parents'])-1; $i>=0; $i-- ) {
				$parent_class = $this->_config[$class_name]['parents'][$i];
				
				// higher priority in the current configuration
				$parent_class_config = $this->_config[$parent_class]['config'];
				$config = array_merge_recursive_distinct_test($parent_class_config, $config);
			}
		}
		
		$this->_config[$class_name]['config'] = $config;
	}
	
	protected function _parseActions($doc_comment) {
		$matches = array();
		$actions = array();
		if ( !preg_match_all('/@action\s+([\w\:\/\*]+)\s+(\w+)(\s+\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER) ) return $actions;
		
		foreach ( $matches as $match ) {
			$actions[] = array(
				'action' => $match[1],
				'method' => $match[2],
				'params' => $this->_parseRules($match[4])
			);
		}
		
		return $actions;
	}
	
	protected function _parseProperties($doc_comment) {
		$matches = array();
		$config = array();
		if ( !preg_match_all('/@var\s+(\w+)(\s+(\w+))?(\s+\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER) ) return false;
		
		if ( count($matches) > 1 ) {	// its object
			foreach ( $matches as $match ) {
				$config['properties'][$match[3]] = array(
					'type' => $match[1]
				);
				
				if ( !isset($match[5]) ) continue;	// no rules
				
				$config['properties'][$match[3]]['rules'] = $this->_parseRules($match[5]);
			}
			if ( !preg_match_all('/@rules\s+\[(.*?)\]/', $doc_comment, $matches, PREG_SET_ORDER) ) return $config;
			
			foreach ( $matches as $rules ) {
				$config['rules'] = array_merge((array)$config['rules'], $this->_parseRules($rules[1]));
			}
		}
		else {
			$config = array(
				'type' => $match[1]
			);
			
			if ( !isset($match[5]) ) return $config;	// no rules
				
			$config['rules'] = $this->_parseRules($match[5]);
		}
		return $config;
	}
	
	protected function _parseRules($rules_string) {
		$res = array();
		if ( !preg_match_all('/(.+?)(,\s+|$)/', $rules_string, $rules, PREG_SET_ORDER) ) return $res;
		
		foreach ( $rules as $rule ) {
			$res[] = $rule[1];
		}
		
		return $res;
	}
}
?>