<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default auth user toke
 *
 * @package	   Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @author	   creatoro
 * @copyright  (c) 2011 creatoro
 * @license	   http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Model_Auth_User_Token extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('token')

			->behaviors(array(
				'auth_user_token' => Jam::behavior('auth_user_token'),
			))

			->fields(array(
				'id' => Jam::field('primary'),
				'user_agent' => Jam::field('string'),
				'token' => Jam::field('string'),
				'type' => Jam::field('string'),
				'created' => Jam::field('timestamp', array(
					'auto_now_create' => TRUE,
				)),
				'expires' => Jam::field('timestamp', array(
					'filters' => array('Model_Auth_User_Token::convert_expires')
				)),
			))

			->associations(array(
				'user' => Jam::association('belongsto'),
			))

			->validator('token', array("unique" => TRUE));
	}

	public static function convert_expires($value)
	{
		if ( ! is_numeric($value) AND $time = strtotime($value))
		{
			return $time;
		}

		return $value;
	}

	public function expired()
	{
		return $this->expires < time();
	}

	public static function generate_token()
	{
		return sha1(uniqid(Text::random('alnum', 32), TRUE));
	}

	public function generate_unique_token()
	{
		do 
		{
			$this->token = Model_Auth_User_Token::generate_token();
			$collection = Jam::all($this->meta()->model())->where_key($this->token)->limit(1);
		} 
		while (Jam::all($this)->where('token', '=', $this->token)->limit(1)->count_all() > 0);

		return $this;	
	}

	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (mt_rand(1, 100) === 1)
		{
			Jam::delete($this)->expired()->execute();
		}
	}
}