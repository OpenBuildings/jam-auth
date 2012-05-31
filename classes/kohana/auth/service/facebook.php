<?php defined('SYSPATH') or die('No direct access allowed.');
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
		require_once Kohana::find_file('vendor/facebook-sdk', 'facebook');
		return new Facebook(Arr::get($this->_config, 'auth'));
	}

	public function logged_in()
	{
		return (bool) $this->api()->getUser();
	}

	public function login_url($back_url)
	{
		return $this->api()->getLoginUrl(array(
			'next' => $back_url
		));
	}

	public function logout_service($request, $back_url)
	{
		if ($request->query('_facebook_logged_out'))
		{
			setcookie('fbs_'.$this->api()->getAppId(), '', time()-100, '/', '.'.parse_url(URL::base(TRUE), PHP_URL_HOST));
			$this->api()->destroySession();
			session_destroy();
			return TRUE;
		}
		else
		{
			$back_url .= (strpos($back_url, '?') === FALSE ? '?' : '&').'_facebook_logged_out=1';

			$request->redirect($this->api()->getLogoutUrl(array(
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
		catch (FacebookApiException $exception) 
		{
			return NULL;			
		}
	}


	public function service_uid()
	{
		return $this->api()->getUser();
	}

} // End Auth Jam