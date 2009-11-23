<?php
require_once('./Object.php');

class URL extends Object {
	
	protected $_raw_url;
	protected $_path;
	protected $_action;
	protected $_state;
	protected $_params;
	protected $_view;
	
	function __construct($url) {
		if ( is_array($url) ) {
			$this->_path = $url['path'];
			$this->_action = $url['action'];
			$this->_state = $url['state'];
			$this->_params = $url['params'];
			$this->_view = $url['view'];
		}
		else {
			$this->_raw_url = $url;			
			$this->_action = 'default';
			$this->_state = 'default';
			$this->_params = array();
			$this->_view = 'html';
		}
	}
	
	function parse() {
		$buf = parse_url($this->_raw_url);
		$this->_path = $buf['path'];
		
		// parse view
		if ( ($start = strrpos($this->_path, '.')) !== false ) {
			$this->_view = substr($this->_path, $start+1);
			$this->_path = substr($this->_path, 0, $start);  
		}
		
		// try to parse url parameters
		$params_re = '/\/-([\w\-]+)(\/([^\/]+))?/';
		$matches = array();
		if ( preg_match_all($params_re, $this->_path, $matches, PREG_SET_ORDER) ) {
			foreach ( $matches as $param ) {
				$this->_params['url'][$param[1]] = $param[3]; 
			}
			$this->_path = preg_replace($params_re,'',$this->_path);
		}
		
		// try to parse action and state
		$action_state_re = '/\/\.([\w\-]+)(\.([\w\-]+))?/';
		if ( preg_match_all($action_state_re, $this->_path, $matches, PREG_SET_ORDER) ) {
			$this->_action = $matches[0][1];
			if ( $matches[0][3] ) $this->_state = $matches[0][3];
			
			$this->_path = preg_replace($action_state_re,'',$this->_path);
		}
		
		$this->_path = preg_replace('/\/+/','/',$this->_path);
		$this->_path .= $this->_path{strlen($this->_path)-1} == '/'? '' : '/';
		
		return $this;
	}
	
	function build() {
		$action_state = !empty($this->_action) ? '.'.$this->_action : '';
		$action_state .= !empty($this->_state) ? '.'.$this->_state : '';
		$this->_raw_url = $this->_path.'/'.$action_state;
		
		$params = array();
		foreach ( $this->_params as $key => $value ) {
			$params[] = '-'.$key.'/'.$value;
		}
		$this->_raw_url .= '/'.implode('/', $params);
		
		$this->_raw_url = preg_replace('/\/+/','/',$this->_raw_url);
		if ( $this->_raw_url{strlen($this->_raw_url)-1} == '/' )
			$this->_raw_url = substr($this->_raw_url, 0, strlen($this->_raw_url)-1);
			
		$this->_raw_url .= '.'.$this->_view;
	}

}

//$url = new URL('http://domain.tld/path/to/some-object/.action.some-state/-qwe/value/-asd/value2/-other-key.json');
//$url->parse();
$url = new URL(array(
	'path' => '/this/is/a/path/to/some-object',
	'action' => 'edit',
	'state' => 'done',
	'params' => array('key'=>'value','qwe'=>'qwe'),
	'view' => 'html'
));
$url->build();
echo print_r($url, 1);

$url = URL::n('http://domain.tld/path/to/some-object/.action.some-state/-qwe/value/-asd/value2/-other-key.json')->parse();
echo print_r($url, 1);
?>