<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Jam Auth driver.
 *
 * @package    Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Auth_Service_Twitter extends Auth_Service {

	protected $_service_field = 'twitter_uid';
	protected $_type = 'twitter';

	public function initialize()
	{
		throw new Kohana_Exception("Twitter auth not yet implemented");
	}

	public function logged_in()
	{
	}

	public function login_url($back_url)
	{
	}

	public function logout_service($request, $back_url)
	{
	}

	public function service_user_info()
	{
	}


	public function service_uid()
	{
	}

} // End Auth Jam