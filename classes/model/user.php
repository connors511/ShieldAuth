<?php
/**
 * Part of Fuel Depot.
 *
 * @package    FuelDepot
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2012 Fuel Development Team
 * @link       http://depot.fuelphp.com
 */

namespace Auth;

class Model_User extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'username',
		'password',
		'group',
		'email',
		'last_login',
		'login_hash',
		'profile_fields' => array(
			'data_type' => 'serialize',
		),
		'created_at',
		'reset_at',
		'confirmed' => array(
			'data_type' => 'bool',
		),
	);

	protected static $_many_many = array(
		'groups'
	);

	protected static $_has_many = array(
		'bans'
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_Typing' => array(
			'events' => array('before_save', 'after_save', 'after_load'),
		),
	);

	public static function _init()
	{
		// make sure the required modules are loaded
		//\Module::load('documentation');
	}

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('username', 'Username', 'required|max_length[255]');
		$val->add_field('full_name', 'Fullname', 'required|max_length[255]');
		$val->add_field('email', 'Email', 'required|valid_email');
		$val->add_field('password', 'Password', 'min_length[8]|match_field[password_again]');

		return $val;
	}

}
