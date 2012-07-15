<?php

namespace Auth;

class Model_Role extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'name',
		'created_at',
		'updated_at',
	);

	protected static $_many_many = array(
		'groups',
		'permissions'
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_Typing' => array(
			'events' => array('before_save', 'after_save', 'after_load'),
		),
	);

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		$val->add_field('name', 'Name', 'required|max_length[255]');

		return $val;
	}

}
