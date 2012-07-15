<?php

namespace Auth;

class Model_Permission extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'name',
		'module',
		'controller',
		'action',
		'allow' => array(
			'data_type' => 'bool',
		),
		'created_at',
		'updated_at',
	);

	protected static $_many_many = array(
		'roles'
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
			'events' => array('before_save', 'after_save', 'after_load')
		)
	);

	public static function validate($factory)
	{
		$val = \Validation::forge($factory);
		//$val->add_field('name', 'Name', 'required|max_length[255]');
		//$val->add_field('roles', 'Roles', 'required');

		return $val;
	}

	public function fqn()
	{
		return ($this->module == '/' ? '' : $this->module) . '/' . $this->controller . '/' . $this->action;
	}

}
