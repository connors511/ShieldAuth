<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_core_namespace('Auth');

Autoloader::add_classes(array(
	'Auth\\Auth_Acl_ShieldAcl'  => __DIR__.'/classes/auth/acl/shieldacl.php',

	'Auth\\Auth_Group_ShieldGroup'  => __DIR__.'/classes/auth/group/shieldgroup.php',

	'Auth\\Auth_Login_ShieldAuth'      => __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserUpdateException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserWrongPasswordException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserNotActivatedException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldExpiredCodeException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserNotLoggedInException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserWrongCodeException'	=> __DIR__.'/classes/auth/login/shieldauth.php',
	'Auth\\ShieldUserNotFoundException'	=> __DIR__.'/classes/auth/login/shieldauth.php',

	'Auth\\Model_User'    => __DIR__.'/classes/model/user.php',
	'Auth\\Model_Group'    => __DIR__.'/classes/model/group.php',
	'Auth\\Model_Role'    => __DIR__.'/classes/model/role.php',
	'Auth\\Model_Permission'    => __DIR__.'/classes/model/permission.php',
	'Auth\\Model_Ban'    => __DIR__.'/classes/model/ban.php',

	'Shield\\Mailer'    => __DIR__.'/classes/mailer.php',
	'NinjAuth\\Adapter_ShieldAuth'  	=> __DIR__.'/classes/adapter/shieldauth.php',
));


/* End of file bootstrap.php */