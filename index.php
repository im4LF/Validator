<?php
require_once("libs/Spyc.class.php");
require_once("libs/utils/array.php");
require_once("qxValidator2.php");

function __autoload($class) {
	echo "load: $class\n";
	if ( !file_exists('./'.$class.'.php') )
		require_once('./classes/'.$class.'.php');
	else
		require_once($class.'.php');
		
	ClassConfig::getInstance()->load($class);
}

$user_init = array (
	'login' => 'myLogin',
	'password' => 'qweqwe',
	'confirm_password' => 'qweqwe1',
	'email' => 'qwe@qwe.qwe',
	'info' => array (
		'surname' => 'mySurname',
		'firstname' => 'myFirstname',
		'lastname' => 'myLastname',
		'org_email' => 'org@qwe.qwe'
	)
);

$userAsPartner = new UserAsPartner($user_init);
echo 'ClassConfig: '.print_r(ClassConfig::getInstance(), true)."\n";

echo str_repeat('-',20)."\n";

$validator = new qxValidator($userAsPartner);
$validator->validate();

/*$login = new Login('test');
$validator = new qxValidator($login);
$validator->validate();*/

//$surname = $validator->getValueByPath('info.surname');
//echo str_repeat('-',20)."\n";
//echo print_r($surname, true);

//var_dump($userAsPartner);
?>