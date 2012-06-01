<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default auth user.
 * 
 * @package	   Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 OpenBuildings Inc.
 * @author	   creatoro
 * @copyright  (c) 2011 creatoro
 * @license	   http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Model_Auth_User extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('username');

		$meta->associations(array(
			'user_tokens' => Jam::association('hasmany'),
			'roles' => Jam::association('manytomany'),
		));

		$meta->fields(array(
			'id' => Jam::field('primary'),
			'email' => Jam::field('email', array(
				'label' => 'email address',
				'rules' => array(
					array('not_empty'),
				),
				'unique' => TRUE,
			)),
			'username' => Jam::field('string', array(
				'label' => 'username',
				'rules' => array(
					array('not_empty'),
					array('max_length', array(':value', 32)),
					array('min_length', array(':value', 3)),
					array('regex', array(':value', '/^[a-zA-Z0-9\_\-]+$/')),
				),
				'unique' => TRUE,
			)),
			'password' => Jam::field('password', array(
				'label' => 'password',
				'rules' => array(
					array('min_length', array(':value', 5)),
					array('max_length', array(':value', 30)),
				),
				'hash_with' => array( Auth::instance(), 'hash'),
			)),
			'logins' => Jam::field('integer', array(
				'default' => 0,
				'convert_empty' => TRUE,
				'empty_value' => 0,
			)),
			'last_login' => Jam::field('timestamp'),
			'facebook_uid' => Jam::field('string'),
			'twitter_uid' => Jam::field('string'),
			'last_login_ip' => Jam::field('string', array(
				'label' => 'Last logged from',
				'rules' => array(
					array('ip')
				)
			)),
		));

		$meta->extend('add_password_validation', "Model_Auth_User::_add_password_validation");
	}

	static public function _add_password_validation(Jam_Meta $meta)
	{
		$meta->extra_rules(array(
			'password' => array(array('not_empty')),
			'password_confirm' => array(
				array('not_empty'),
				array('min_length', array(':value', 5)),
				array('matches', array(':validation', ':field', 'password'))
			),
		));
	}

	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login()
	{
		if ($this->loaded())
		{
			// Update the number of logins
			$this->logins = $this->logins + 1;

			// Set the last login date
			$this->last_login = time();

			// Save the user
			$this->save();
		}
	}

	public function load_service_values(Auth_Service $service, array $user_data, $create = FALSE)
	{
		
	}

	public function generate_login_token()
	{
		return $this->user_tokens->build()->create_token();
	}
	
	public function has_facebook()
	{
		return (bool) $this->facebook_uid;
	}

} // End Kohana_Model_Auth_User