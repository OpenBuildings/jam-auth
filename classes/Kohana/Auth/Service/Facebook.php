<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Jam Auth driver.
 *
 * @package    Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Auth_Service_Facebook extends Auth_Service {

	protected $_service_field = 'facebook_uid';

	protected $_type = 'facebook';

	public function initialize()
	{
		return new Facebook(Arr::get($this->_config, 'auth'));
	}

	public function logged_in()
	{
		return (bool) $this->api()->getUser();
	}

	public function login_url($back_url)
	{
		return $this->api()->getLoginUrl(array(
			'redirect_uri' => $back_url,
		));
	}

	public function logout_service($back_url)
	{
		HTTP::redirect($this->api()->getLogoutUrl(array(
			'redirect_uri' => $back_url
		)));
	}

	public function service_user_info()
	{
		try 
		{
			return $this->api()->api('/me');
		} 
		catch (FacebookApiException $exception) {}
	}

	public function service_uid()
	{
		return $this->api()->getUser();
	}

} // End Auth Jam