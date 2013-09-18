<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default auth user.
 * 
 * @package	   Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @author	   creatoro
 * @copyright  (c) 2011 creatoro
 * @license	   http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Model_Auth_User extends Jam_Model {

	public $validate_password = FALSE;

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('username')

			->associations(array(
				'user_tokens' => Jam::association('hasmany'),
				'roles' => Jam::association('manytomany'),
			))

			->fields(array(
				'id' => Jam::field('primary'),
				'email' => Jam::field('string', array(
					'label' => 'email address',
				)),
				'username' => Jam::field('string'),
				'password' => Jam::field('password', array(
					'hash_with' => array(Auth::instance(), 'hash'),
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
			))

		
			->validator('email', array(
				'format' => array('email' => TRUE),
				'unique' => TRUE
			))
			->validator('username', array(
				'length' => array('minimum' => 3, 'maximum' => 32),
				'present' => TRUE,
				'format' => array('regex' => '/^[a-zA-Z0-9\_\-]+$/')
			))
			->validator('password', array(
				'length' => array('minimum' => 5, 'maximum' => 30),
				'if' => 'validate_password',
			))
			->validator('last_login_ip', array(
				'format' => array('filter' => FILTER_VALIDATE_IP),
			))
			->validator('password', array(
				'if' => 'validate_password',
				'present' => TRUE,
				'confirmed' => TRUE,
			))
			->validator('password_confirmation', array(
				'present' => TRUE,
				'if' => 'validate_password',
			));
	}

	public static function unique_key($value)
	{
		return Valid::email($value) ? 'email' : ((is_numeric($value) OR $value === NULL) ? 'id' : 'username');
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

	public function build_user_token(array $values = array())
	{
		return $this->user_tokens->build(Arr::merge(array(
			'expires' => time() + Kohana::$config->load('auth.lifetime'),
		), $values))->generate_unique_token();
	}
	
	public function has_facebook()
	{
		return (bool) $this->facebook_uid;
	}

} // End Kohana_Model_Auth_User