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

	public $validate_password = FALSE;

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
			'email' => Jam::field('string', array(
				'label' => 'email address',
			)),
			'username' => Jam::field('string', array(
				'label' => 'username',
				'unique' => TRUE,
			)),
			'password' => Jam::field('password', array(
				'label' => 'password',
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
			'last_login_ip' => Jam::field('string', array('label' => 'Last logged from')),
		));

		$meta
			->validator('email', array(
				'format' => array('filter' => FILTER_VALIDATE_EMAIL),
				'unique' => TRUE
			))
			->validator('username', array(
				'length' => array('minimum' => 3, 'maximum' => 32),
				'present' => TRUE,
				'format' => array('regex' => '/^[a-zA-Z0-9\_\-]+$/')
			))
			->validator('password', array(
				'length' => array('minimum' => 5, 'maximum' => 30),
			))
			->validator('last_login_ip', array(
				'format' => array('filter' => FILTER_VALIDATE_IP),
			))
			->validator('password', array(
				'if' => 'validate_password',
				'present' => TRUE,
				'confirmed' => TRUE,
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