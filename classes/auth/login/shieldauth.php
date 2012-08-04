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


class ShieldUserUpdateException extends \FuelException {}

class ShieldUserWrongPasswordException extends \FuelException {}

class ShieldUserNotActivatedException extends \FuelException {}

class ShieldExpiredCodeException extends \FuelException {}

class ShieldUserNotLoggedInException extends \FuelException {}

class ShieldUserWrongCodeException extends \FuelException {}

class ShieldUserNotFoundException extends \FuelException {}

/**
 * ShieldAuth basic login driver
 *
 * @package     Fuel
 * @subpackage  Auth
 */
class Auth_Login_ShieldAuth extends \Auth_Login_Driver
{

	public static function _init()
	{
		\Config::load('shieldauth', true, true, true);
	}

	/**
	 * @var  Database_Result  when login succeeded
	 */
	protected $user = null;

	/**
	 * @var  array  ShieldAuth class config
	 */
	protected $config = array(
		'drivers' => array('group' => array('ShieldGroup')),
		'additional_fields' => array('profile_fields'),
	);

	/**
     * Check for login
     *
     * @return  bool
     */
    protected function perform_check()
    {
        $username    = \Session::get('username');
        $login_hash  = \Session::get('login_hash');

        // only worth checking if there's both a username and login-hash
        if ( ! empty($username) and ! empty($login_hash))
        {
            if (is_null($this->user) or $this->user->username != $username)
            {
                $this->user = Model_User::find_by_username($username);
            }

            // return true when login was verified
            if ($this->user and $this->user->login_hash === $login_hash)
            {
                return true;
            }
        }

        // no valid login when still here, ensure empty session
        $this->user = false;
        \Session::delete('username');
        \Session::delete('login_hash');

        return false;
    }

    /**
     * Check the user exists before logging in
     *
     * @return  bool
     */
    public function validate_user($username_or_email = '', $password = '')
    {
        $username_or_email = trim($username_or_email) ?: trim(\Input::post(\Config::get('shieldauth.username_post_key', 'username')));
        $password = trim($password) ?: trim(\Input::post(\Config::get('shieldauth.password_post_key', 'password')));

        if (empty($username_or_email) or empty($password))
        {
            return false;
        }

        $password = $this->hash_password($password);
        $this->user = Model_User::find()
                    ->where_open()
                    ->where('username', '=', $username_or_email)
                    ->or_where('email', '=', $username_or_email)
                    ->where_close()
                    ->where('password', '=', $password)
                    ->get_one();

        return $this->user ?: false;
    }

    /**
     * Login user
     *
     * @param   string
     * @param   string
     * @return  bool
     */
    public function login($username_or_email = '', $password = '')
    {
        if ( ! ($this->user = $this->validate_user($username_or_email, $password)))
        {
            $this->_run_event('failed_login');
            $this->user = false;
            \Session::delete('username');
            \Session::delete('login_hash');
            return false;
        }

        $this->_run_event('successful_login');
        \Session::set('username', $this->user->username);
        \Session::set('login_hash', $this->create_login_hash());
        \Session::instance()->rotate();
        return true;
    }

    /**
     * Force login user
     *
     * @param   string
     * @return  bool
     */
    public function force_login($user_id = '')
    {
        if (empty($user_id))
        {
            return false;
        }

        $this->user = Model_User::find($user_id);

        if ($this->user == false)
        {
            $this->user = false;
            \Session::delete('username');
            \Session::delete('login_hash');
            return false;
        }

        $this->_run_event('on_forced_login');

        \Session::set('username', $this->user->username);
        \Session::set('login_hash', $this->create_login_hash());
        return true;
    }

    /**
     * Logout user
     *
     * @return  bool
     */
    public function logout()
    {
        $this->_run_event('on_logout');

        $this->user = false;
        \Session::delete('username');
        \Session::delete('login_hash');
        return true;
    }

	/**
     * Create new user
     *
     * @param   string
     * @param   string
     * @param   string  must contain valid email address
     * @param   int     group id
     * @param   Array
     * @return  bool
     */
    public function create_user($username, $password, $email, $group = 0, Array $profile_fields = array())
    {
        $password = trim($password);
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (empty($username) or empty($password) or empty($email))
        {
            throw new ShieldUserUpdateException('Username, password and email address can\'t be empty.', 1);
        }

        $same_users = Model_User::find()
                    ->where('username', '=', $username)
                    ->or_where('email', '=', $email);

        if ($same_users->count() > 0)
        {
            if (in_array(strtolower($email), array_map('strtolower', $same_users->current())))
            {
                throw new ShieldUserUpdateException('Email address already exists', 2);
            }
            else
            {
                throw new ShieldUserUpdateException('Username already exists', 3);
            }
        }

        $user = Model_User::forge(
                    array(
                        'username'        => (string) $username,
                        'password'        => $this->hash_password((string) $password),
                        'email'           => $email,
                        'group'           => (int) $group,
                        'profile_fields'  => serialize($profile_fields)
                    )
                );
        if (!\Config::get('shieldauth.confirmable.in_use'))
        {
            $user->confirmed = true;
        }
        $user->save();

        $this->_run_event('after_create_user');

        return $user->id;
    }

	/**
	 * Deletes a given user
	 *
	 * @param   string
	 * @return  bool
	 */
	public function delete_user($username)
	{
		if (empty($username))
		{
			throw new ShieldUserUpdateException('Cannot delete user with empty username', 9);
		}
        
        $this->_run_event('before_delete_user');
		
        $user = Model_User::find_by_username($username);

		return $user->delete();
	}

	/**
	 * Creates a temporary hash that will validate the current login
	 *
	 * @return  string
	 */
	public function create_login_hash()
    {
        if (empty($this->user))
        {
            throw new ShieldUserUpdateException('User not logged in, can\'t create login hash.', 10);
        }

        $last_login = \Date::forge()->get_timestamp();
        $login_hash = sha1(\Config::get('shieldauth.login_hash_salt').$this->user->username.$last_login);

        $this->user->last_login = $last_login;
        $this->user->login_hash = $login_hash;
        $this->user->save();

        return $login_hash;
    }

    /**
     * Get the user's ID
     *
     * @return  Array  containing this driver's ID & the user's ID
     */
    public function get_user_id()
    {
        if (empty($this->user))
        {
            return false;
        }

        return array($this->id, (int) $this->user->id);
    }

    /**
     * Get the user object
     *
     * @return  Model_User  the user
     */
    public function get_user()
    {
        if (empty($this->user))
        {
            return false;
        }

        return $this->user;
    }

	/**
	 * Get the user's groups
	 *
	 * @return  Array  containing the group driver ID & the user's group ID
	 */
	public function get_groups()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array(array('ShieldGroup', array_keys(\Arr::assoc_to_keyval($this->user->groups, 'id', 'name'))));
	}

	/**
	 * Get the user's emailaddress
	 *
	 * @return  string
	 */
	public function get_email()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->email;
	}

	/**
	 * Get the user's screen name
	 *
	 * @return  string
	 */
	public function get_screen_name()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->username;
	}

	/**
	 * Get the user's profile fields
	 *
	 * @return  Array
	 */
	public function get_profile_fields()
	{
		if (empty($this->user))
		{
			return false;
		}

		if (isset($this->user->profile_fields))
		{
			is_array($this->user->profile_fields) or $this->user->profile_fields = @unserialize($this->user->profile_fields);
		}
		else
		{
			$this->user->profile_fields = array();
		}

		return $this->user->profile_fields;
	}

	/**
	 * Extension of base driver method to default to user group instead of user id
	 */
	public function has_access($condition = null, $driver = null, $user = null)
	{
		$module = '';
        $controller = '';
        $action = '';

        $arr = $condition;
        if (is_array($arr))
        {
            // Multiple permissions?
            if (count($arr) == 1 and is_array($arr[0]))
            {
                $arr = $arr[0];
            }

            if (count($arr) == 3)
            {
                list($module, $controller, $action) = $arr;
            }
            else if (count($arr) == 2)
            {
                list($controller, $action) = $arr;
            }
            else
            {
                throw BadFunctionCallException();
            }
        }
        elseif ($arr == null)
        {
            // ::active to support HMVC requests
            list($module, $controller) = explode('\\', \Request::active()->controller);
            $action = \Request::active()->action;
        }
        else
        {
            list ($a, $b, $c) = explode('/', $arr);
            if (empty($c)) {
                $controller = $a;
                $action = $b;
            }
            else
            {
                $module = $a;
                $controller = $b;
                $action = $c;
            }
        }
        
        $condition = "{$module}\\{$controller}\\{$action}";

        $this->_run_event('on_has_access');

		return parent::has_access($condition, $driver == null ? 'ShieldAcl' : $driver, $user);
	}

    public function generate_reset_code($user = null)
    {
        if (!\Config::get('shieldauth.recoverable.in_use'))
        {
            return false;
        }

        if ($user == null)
        {
            throw new ShieldUserNotLoggedInException();
        }

        $user->reset_at = \Date::time('UTC')->get_timestamp();
        $user->save();
        $code = sha1('reset' . $user->reset_at . $user->id);

        return $code;
    }

    public function generate_confirm_code($user = null)
    {
        if (!\Config::get('shieldauth.confirmable.in_use'))
        {
            return false;
        }
        if ($user == null)
        {
            throw new ShieldUserNotLoggedInException();
        }

        $code = sha1('confirm' . $user->created_at . $user->id);

        return $code;
    }

    public function verify_reset_code($code)
    {
        if (!\Config::get('shieldauth.recoverable.in_use'))
        {
            return false;
        }

        if (empty($this->user))
        {
            $user = Model_User::query()
                ->where(\DB::expr('SHA1(CONCAT("reset", reset_at, id))'), '=', $code)
                ->get_one();

            if ($user == null)
            {
                throw new ShieldUserWrongCodeException();
            }
        }
        else
        {
            $user = $this->user;
        }

        if (sha1('reset' . $user->reset_at . $user->id) == $code)
        {
            $lifetime = \Config::get('shieldauth.recoverable.reset_password_within');
            $expires  = strtotime($lifetime, $user->reset_at);
            if ($expires >= time())
            {
                return true;
            }

            throw new ShieldExpiredCodeException();
        }

        return false;
    }

    public function change_password_by_code($code, $password)
    {
        if ($this->verify_reset_code($code))
        {
            if (empty($this->user))
            {
                $user = Model_User::query()
                    ->where(\DB::expr('SHA1(CONCAT("reset", reset_at, id))'), '=', $code)
                    ->get_one();

                if ($user == null)
                {
                    throw new ShieldUserWrongCodeException();
                }
            }
            else
            {
                $user = $this->user;
            }

            $user->reset_at = null;
            $user->password = $this->hash_password((string) $password);
            $user->save();
            return true;
        }
        return false;
    }

    public function verify_confirm_code($code)
    {
        if (!\Config::get('shieldauth.confirmable.in_use'))
        {
            return false;
        }
        if (empty($this->user))
        {
            $user = Model_User::query()
                ->where(\DB::expr('SHA1(CONCAT("confirm", created_at, id))'), '=', $code)
                ->get_one();

            if ($user == null)
            {
                throw new ShieldUserWrongCodeException();
            }
        }
        else
        {
            $user = $this->user;
        }

        if (sha1('confirm' . $user->created_at . $user->id) == $code)
        {
            $user->confirmed = true;
            $user->save();
            return true;
        }

        return false;
    }

    public function send_reset_mail($user = null)
    {
        $code = $this->generate_reset_code($user);
        return \Shield\Mailer::send_reset_instructions($code, $user);
    }

    public function send_confirm_mail($user = null)
    {
        $code = $this->generate_confirm_code($user);
        return \Shield\Mailer::send_confirm_instructions($code, $user);
    }

    public function reset_password($username_or_email)
    {
        $user = Model_User::find()
                ->where('username', '=', $username_or_email)
                ->or_where('email', '=', $username_or_email)
                ->get_one();

        if ($user == null)
        {
            throw new ShieldUserNotFoundException();
        }

        $this->send_reset_mail($user);
    }

    /**
     * Runs a Shield callback event if its been registered
     *
     * @param string $name The event to run
     */
    private function _run_event($name)
    {
        $event = "shield_{$name}";

        if (\Event::has_events($event)) {
            \Event::trigger($event, $this->user);
        }
    }
}

// end of file shieldauth.php
