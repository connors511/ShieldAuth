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

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(

	/**
	 * DB table name for the user table
	 */
	'table_name' => 'users',

	/**
	 * Choose which columns are selected, must include: username, password, email, last_login,
	 * login_hash, group & profile_fields
	 */
	'table_columns' => array('*'),

	/**
	 * This will allow you to use the group & acl driver for non-logged in users
	 */
	'guest_login' => true,

	/**
	 * Salt for the login hash
	 */
	'login_hash_salt' => 'put_some_salt_in_here',

	/**
	 * $_POST key for login username
	 */
	'username_post_key' => 'username',

	/**
	 * $_POST key for login password
	 */
	'password_post_key' => 'password',
	
	'access' => array(
		/**
		 * Default access if requested role not defined for user.
		 * @val true | false
		 */
		'default' => false
	),

	/**
     * Recoverable takes care of resetting the user password.
     */
    'recoverable' => array(
        /**
         * Set to true, to enable
         *
         * (bool)
         */
        'in_use'   => true,

        /**
         * The limit time within which the reset password token is valid.
         * Must always be a valid php date/time value.
         * Default is '+1 week'.
         *
         * @see http://www.php.net/manual/en/datetime.formats.php
         *
         * (string)
         */
        'reset_password_within' => '+1 week',

        /**
         * The url a user will be taken to reset their password for their account.
         * This url will also be included in the mail appended by the reset password token.
         * eg. reset_password
         *
         * (string)
         */
        'url' => 'users/reset/code/'
    ),

    /**
     * Confirmable is responsible to verify if an account is already confirmed to
     * sign in
     */
    'confirmable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => true,

        /**
         * The limit time within which the confirmation token is valid.
         * Must always be a valid php date/time value.
         * Default is '+1 week'.
         *
         * @see http://www.php.net/manual/en/datetime.formats.php
         *
         * (string)
         */
        'confirm_within' => '+1 week',

        /**
         * The url a user will be taken to confirm their account.
         * This url will also be included in the mail appended by the confirmation token.
         * eg. confirmation
         *
         * (string)
         */
        'url' => ''
    ),
);
