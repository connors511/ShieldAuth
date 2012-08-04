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

namespace Auth;


class Auth_Acl_ShieldAcl extends \Auth_Acl_Driver
{

	protected static $_valid_roles = array();

	public static function _init()
	{
		static::$_valid_roles = array_keys(\Arr::assoc_to_keyval(Model_Role::find('all'), 'id', 'name'));
		\Config::load('shieldauth', true, true, true);
	}

	public function has_access($condition, Array $entity)
	{
		$groups = \Auth::get_groups($entity[0]);

		list($module, $controller, $action) = explode('\\', $condition);
		
		$roles = Model_Permission::find()
				->related('roles')
				->related('roles.groups')
				->related('roles.groups.users')
				->where('roles.groups.users.id', '=', Auth::get_user()->id)
				->where(\DB::expr("'{$module}'"),'REGEXP', \DB::expr("CONCAT('^',REPLACE(module, '*', '.*') ,'$')"))
				->where(\DB::expr("'{$controller}'"),'REGEXP', \DB::expr("CONCAT('^',REPLACE(controller, '*', '.*') ,'$')"))
				->where(\DB::expr("'{$action}'"),'REGEXP', \DB::expr("CONCAT('^',REPLACE(action, '*', '.*') ,'$')"))
				->get();

        if ($roles != null)
        {
            foreach($roles as $r)
            {
                if (!!$r->allow) {
                    // One allow is enough to grant access
                    return true;
                }
            }
        }

        // Return default if no roles are found
        return $roles != null || \Config::get('shieldauth.access.default');
	}
}

/* end of file shieldacl.php */
