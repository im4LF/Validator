<?php
class qxValidator {
	
	protected $_value;
	protected $_class_of_value;
	protected $_rules;
	protected $_results;
	
	function __construct(&$value) {
		$this->_value =& $value;
		$this->_class_of_value = get_class($this->_value);
		
		$rules_builder = new qxRulesBuilder(ClassConfig::getInstance()->load($this->_class_of_value));
		$rules_builder->build();
		$this->_rules['data'] = $rules_builder->rules;
	}
	
	function validate() {
		echo print_r($this->_rules, true);
		foreach ( $this->_rules['data'] as $path => $rules ) {
			$this->_applyRuleByPath($path);
		}
		echo print_r($this->_results, true);
	}
	
	protected function _applyRuleByPath($path, $called_by_path = '') {
		if ( $this->_rules['locks'][$path] ) {
			die('deadlock:'.$path.', called by: '.$called_by_path);
		}
		
		$this->_rules['locks'][$path] = true;
		foreach ( $this->_rules['data'][$path] as $rule ) {
			$this->_applyRule($rule);
		}
		$this->_rules['locks'][$path] = false;
		$this->_rules['applied'][$path] = true;
	}
	
	protected function _applyRule($rule) {
		echo 'call "'.$rule->path.'.'.$rule->name."()\"\n";
		// if rule contains pointers
		if ( count($rule->pointers) ) {
			$values = array();
			// throughout all pointers
			foreach ( $rule->pointers as $pointer ) {
				if ( !$this->_rules['applied'][$pointer['path']] ) {
					$this->_applyRuleByPath($pointer['path'], $rule->path);
				}
				$buf = null;
				eval('$buf = $this->_value'.str_replace('.','->',$pointer['path']).'->valueOf();');
				if ( gettype($buf) == 'string' )
					$buf = '"'.addslashes($buf).'"';
				$values[] = $buf; 
			}
			//echo print_r($values, true);
			$rule->applyPointers($values);
		}

		$object = null;
		$args = array();
		$code = '$object =& $this->_value'.str_replace('.','->',$rule->path).';'; 
		eval($code);
		$code = '$args = array('.$rule->args.');';
		eval($code);
		
		echo print_r($object, true);
		echo print_r($args, true);
		ob_start();
			var_dump(call_user_method_array($rule->name, $object, $args));
			$res = ob_get_contents();
		ob_clean();
		$this->_results[$rule->path][$rule->name] = $res;
	}

}

class qxValidationResult {
	
}

class qxRulesBuilder {
	
	protected $_config;
	protected $_rules;
	
	function __construct($config) {
		$this->_config = $config;
	}
	
	function __get($name) {
		return $this->{'_'.$name};
	}
	
	function build() {
		$this->_buildRules($this->_config);
	}
	
	protected function _buildRules($config, $rule_path = '') {
		if ( isset($config['fields']) ) // if configuration have fields - its object
			$this->_buildObjectRules($config, $rule_path);
		else // its scalar value
			$this->_buildScalarRules($config, $rule_path);
	}
	
	protected function _buildObjectRules($config, $rule_path = '') {
		// throughout all fields 
		foreach ( $config['fields'] as $field => $params ) {
			$type = $params['type']; // type of field
			
			$current_rule_path = $rule_path.'.'.$field; // new rule path
			$this->_buildRules(ClassConfig::getInstance()->load($type), $current_rule_path); // build rules for type of field 

			// if now rules defined for current field - continue
			if ( !isset($params['rules']) )
				continue;
			
			// throughout all rules defined for current field
			foreach ( $params['rules'] as $rule ) {
				$this->_setupRule($rule, $current_rule_path);
			}
		}
		// throughout all rules defined for current object
		foreach ( $config['rules'] as $rule ) {
			$this->_setupRule($rule, $rule_path);
		}
	}
	
	protected function _buildScalarRules($config, $rule_path = '') {
		if ( !isset($config['rules']) )
			return;
		
		foreach ( $config['rules'] as $rule ) {
			$this->_setupRule($rule, $rule_path, $value);			
		}  
	}
	
	protected function _setupRule($rule, $defined_at) {
		$parsed_rule = new qxRule($rule, $defined_at);
		
		// if rule path defined as mask
		if ( strpos($parsed_rule->path, '*') !== false ) {
			$rule_path_mask = str_replace('.', '\.', $parsed_rule->path);
			$rule_path_mask = str_replace('*', '.*', $parsed_rule->path);
			foreach ( $this->_rules as $key => $rules ) {
				if ( !eregi('^'.$rule_path_mask.'$', $key) ) continue;
				
				$parsed_rule->path = $key;
				$this->_addRule($parsed_rule);
			}
		}
		else
			$this->_addRule($parsed_rule);
	}
	
	protected function _addRule($rule) {
		$function = '_insertRule';
		if ( $rule->mode == '+' ) {
			$rule->position = ($rule->raw_position == 'first' ? 0 : count($this->_rules[$rule->path]));
		}
		elseif ( $rule->mode == '-' ) {
			$function = '_removeRule';			
		}
		elseif ( $rule->mode == '<' || $rule->mode == '>' ) {
			for ( $i=0; $i<count($this->_rules[$rule->path]); $i++ ) {
				if ( $rule->name != $this->_rules[$rule->path][$i]->name ) continue;
					
				$rule->position = $rule->mode == '<' ? $i-1 : $i+1;
				break;
			}
			
			$rule->position = (int) $rule->position;
		}
		
		$this->{$function}($rule);		
	}
	
	protected function _insertRule($rule) {
		$this->_rules[$rule->path] = array_insert((array) $this->_rules[$rule->path], $rule, $rule->position);
	}
	
	protected function _removeRule($rule) {
		$rule_mask = str_replace('*', '.*', $rule->name);
			
		for( $i=0; $i<count($this->_rules[$rule->path]); $i++ ) {
			if ( !eregi('^'.$rule_mask.'$', $this->_rules[$rule->path][$i]->name) ) continue;
				
			unset($this->_rules[$rule->path][$i]);
		}
	}
	
}

class qxRule {
	
	protected $_raw_rule;
	protected $_raw_args;
	protected $_raw_position;
	
	protected $_name;
	protected $_path;
	protected $_defined_at;
	protected $_args;
	protected $_position;
	protected $_mode;
	
	protected $_pointers;
	
	function __construct($rule, $defined_at) {
		$this->_raw_rule = $rule;
		$this->_defined_at = $defined_at;
		$this->_parse();
	}
	
	function __get($name) {
		return $this->{'_'.$name};
	}
	
	function __set($name, $value) {
		$this->{'_'.$name} = $value;
	}
	
	function applyPointers($values) {
		$n = count($this->_pointers);
		do {
			$value = array_shift($values);
						
			$this->_args = preg_replace('/([^\\\\])(\.[\w\.]+)/', $this->_pointers[$i]['prefix'].$value, $this->_args, 1);
			$n++;
		}
		while ( $n > 0 && count($values) > 0 );
		echo $this->_args;
	}
	
	function buildArgs(&$value) {
		$matches = array();
		
		// if arguments have pointer to other values
		if ( $count > 0 ) {
			$pointers = array();
			foreach ( $matches as $pointer ) {
				
			}
		}
	}
	
	protected function _parse() {		
		$rule = trim($this->_raw_rule);
		
		$this->_mode = '+';
		$this->_pointers = array();
		
		$matches = array();		
		$count = preg_match_all('/\((.*)\)/', $rule, $matches, PREG_SET_ORDER);	// detect arguments of rule
		
		// if arguments exists
		if ( $count > 0 ) {
			$this->_raw_args = ' '.trim($matches[0][1]);	// save raw arguments	
			$this->_args = $this->_raw_args;		
			$rule = preg_replace('/\((.*)\)/', '', $rule);	// cut arguments from rule string
			preg_match_all('/([^\\\\])(\.[\w\.]+)/', $this->_raw_args, $this->_pointers, PREG_SET_ORDER); // get all pointers
		}
		$buf = '';		
		list($buf, $this->_raw_position) = explode('@', $rule);	// if setup position - save if
		
		if ( $buf{0} == '+' || $buf{0} == '-' || $buf{0} == '>' || $buf{0} == '<' ) {
			$this->_mode = $buf{0};
			$buf = substr($buf, 1);
		}
		$this->_name = $buf;
		if ( ($epos = strrpos($buf, '.')) !== false ) {		
			$this->_name = substr($buf, $epos+1);	// get name of rule - string after last "."
			$this->_path = substr($buf, 0, $epos);	// get path of rule
		}
		$this->_path = $this->_defined_at.(substr_count($this->_path, '.') || strlen($this->_path) ? '.'.$this->_path : '');
		
		// build absolute paths for all pointers
		foreach ( $this->_pointers as $id => $pointer ) {
			$arg_path = $pointer[2];
			if ( $this->_defined_at != $this->_path ) {
				$arg_path = $this->_defined_at.'.'.$pointer[2];
			}
			$this->_pointers[$id] = array(
				'prefix' => $pointer[1],
				'path' => str_replace('..','.',$arg_path)
			);
		}		
	}
	
}
?>