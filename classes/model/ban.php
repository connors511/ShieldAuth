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

class Model_Ban extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'duration',
		'reason',
		'unban_reason',
		'active' => array(
			'data_type' => 'bool',
		),
		'created_at',
		'user_id'
	);

	protected static $_belongs_to = array(
		'user'
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

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('duration', 'Duration', 'required|valid_string[numeric]');
		$val->add_field('reason', 'Reason', 'required');

		return $val;
	}
}
