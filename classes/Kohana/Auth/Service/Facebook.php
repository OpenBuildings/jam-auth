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
		return new Facebook\Facebook(Arr::get($this->_config, 'auth'));
	}

	public function logged_in()
	{
		return (bool) $this->service_uid();
	}

	public function login_url($back_url)
	{
		$helper = $this->api()->getRedirectLoginHelper();

		$permissions = ['email'];

		return $helper->getLoginUrl($back_url, $permissions);
	}

	public function logout_service($request, $back_url)
	{
		$helper = $this->api()->getRedirectLoginHelper();

		$accessToken = $helper->getAccessToken();

		$logoutUrl = $helper->getLogoutUrl($accessToken, $back_url);

		HTTP::redirect($logoutUrl);

		return FALSE;
	}

	public function get_user_node()
	{
		try {
		  $result = $this->api()->get('/me?fields=id,first_name,last_name,email,name');

		} catch(Facebook\Exceptions\FacebookResponseException $exception) {
			throw new Auth_Exception_Service($exception->getMessage(), [], 0, $exception);
		} catch(Facebook\Exceptions\FacebookSDKException $exception) {
		  throw new Auth_Exception_Service($exception->getMessage(), [], 0, $exception);
		}

		return $result->getGraphObject();
	}

	public function service_user_info()
	{
		return $this->get_user_node()->asArray();
	}

	public function service_login_complete()
	{
		$helper = $this->api()->getRedirectLoginHelper();

		try {
		  $accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $exception) {
			throw new Auth_Exception_Service($exception->getMessage(), [], 0, $exception);
		} catch(Facebook\Exceptions\FacebookSDKException $exception) {
		  throw new Auth_Exception_Service($exception->getMessage(), [], 0, $exception);
		}

		$this->api()->setDefaultAccessToken($accessToken);
	}

	public function service_uid()
	{
		if ($this->api()->getDefaultAccessToken()) {
			return $this->get_user_node()->getField('id');
		}
	}

} // End Auth Jam
