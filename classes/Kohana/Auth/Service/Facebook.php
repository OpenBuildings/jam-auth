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

	const LOGOUT_PARAMETER = '_facebook_logged_out';

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

	public function logout_service($request, $back_url)
	{
		if ($request->query(Auth_Service_Facebook::LOGOUT_PARAMETER))
		{
			$this->api()->destroySession();
			return TRUE;
		}
		else
		{
			$back_url .= (strpos($back_url, '?') === FALSE ? '?' : '&').Auth_Service_Facebook::LOGOUT_PARAMETER.'=1';

			HTTP::redirect($this->api()->getLogoutUrl(array(
				'next' => $back_url
			)));
			return FALSE;
		}
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