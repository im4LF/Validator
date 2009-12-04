<?php
require_once('libs/Spyc.class.php');
/*
class qxRouter {
	
}

class qxValidator {
	
	protected static $_instance = null;
	protected $_as_class = null;
	protected $_results = array();
	
	public static function getInstance() {
		if ( !self::$_instance ) {
			$class = __CLASS__; 
			self::$_instance = new $class;
		}
		return self::$_instance;
	}
	
	public static function init($value, $as_class) {
		self::getInstance();
		self::$_instance->_as_class = new $as_class($value);
		return self::$_instance;
	}
	
	function __call($name, $args) {
		echo "$name\n";
		$res = call_user_method_array($name, $this->_as_class, $args);
		$this->_results[$res][] = $name; 
		return $this;
	}
	
	function validate() {
		echo print_r($this->_results, true);
	}
}

class qxScalar {
	protected $_value;
	
	function __construct($value) {
		$this->_value = $value;
	}
	
	function valueOf() {
		return $this->_value;
	}
}

class qxString extends  qxScalar {
	function __construct($value) {
		parent::__construct($value);
	}
	
	function required() {
		return strlen($this->_value) > 0 ? true : false;	
	}
	
	function min($length) {
		return strlen($this->_value) >= $length ? true : false;
	} 
}

class Login extends qxString {
	function alpha_digit_underscope() {
		return false;
	}
}

$var = 'some_value';

$var = qxValidator::init($var, 'Login')->required()->min(6)->alpha_digit_underscope()->validate();*/

/**
 * 
 * @param User $user:post [login.required]
 * @param String $confirm_password:post [required, eq($user.password), range(6,15)]
 * @rules $user.password.required, $user.email.required
 * @return 
 */
function registerUser($user, $confirm_password) {
	
}

class Scalar {}
class Object {
	protected $_fields;
	
	function __construct() {
		$reflection = new ReflectionObject($this);
		echo $reflection->getDocComment();
		echo $reflection->getParentClass()->getName();
	}
}

class String extends Scalar {
	function __construct($value) {
	}
}

/**
 * @var String [alpha_digit_underscore]
 */
class Login extends String {}

/**
 * @var String [valid]
 */
class Email extends String {}

/**
 * @var String [min(6)]
 */
class Password extends String {}

/**
 * @var String surname 
 * @var String firstname
 * @var String lastname
 */
class UserInfo extends Object {}

/**
 * @var Login login
 * @var Password password 
 * @var UserInfo info [surname.required, firstname.min(2), lastname.min(2)]
 */
class User extends Object {}

/**
 * @var Email login [exist]
 */
class AdvUser extends User {}

class ClassConfig {
	protected $_config;
	
	function __construct() {
		$this->_config = array();
	}
	
	function load($class) {
		$reflection = null;
		if ( is_object($class) ) {
			$class = get_class($class);
		}
		
		if ( array_key_exists($class, $this->_config) ) 
			return $this->_config[$class]['config'];
		
		$reflection = new ReflectionClass($class);
		
		if ( !$reflection )
			return false;
		
		$this->_config[$class]['reflection'] = $reflection;
		$this->parse($class);
	} 
	
	protected function parse($class) {
		$doc_comment = $this->_config[$class]['reflection']->getDocComment();
		echo $doc_comment;
		
		$matches = array();
		$config = array();
		if ( preg_match_all('/@var\s+([\w]+)\s+([\w]+)\s+(\[(.*?)\])?/', $doc_comment, $matches, PREG_SET_ORDER) ) {
			echo print_r($matches, 1);
			foreach ( $matches as $buf ) {
				$config['properties'][$buf[2]]['type'] = $buf[1];
				
				if ( !isset($buf[4]) ) continue;
					
				if ( !preg_match_all('/(.+?)(,\s|$)/', $buf[4], $rules, PREG_SET_ORDER) ) continue;
				
				echo print_r($rules, 1);
				foreach ( $rules as $rule ) {
					$config['properties'][$buf[2]]['rules'][] = $rule[1];
				}
			}
			$this->_config[$class]['config'] = $config;
		}
	}
}

$config = new ClassConfig;
$config->load('User');
echo print_r($config,1);

$data = array(
	'user' => array(
		'login' => 'SomeLogin',
		'password' => 'myPassword',
		'userinfo' => array(
			'surname' => 'Surname',
			'firstname' => 'Firstname',
			'lastname' => 'Lastname'
		)
	),
	'confirm_password' => 'confirm'
);

$data = array(
	'user.login' => 'SomeLogin',
	'user.password' => 'myPassword',
	'user.userinfo.surname' => 'Surname',
	'user.userinfo.firstname' => 'Firstname',
	'user.userinfo.lastname' => 'Lastname',
	'confirm_password' => 'confirm'
);
?>