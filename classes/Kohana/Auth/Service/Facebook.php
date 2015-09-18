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

		return $helper->getLoginUrl($back_url);
	}

	public function logout_service($request, $back_url)
	{
		$helper = $this->api()->getRedirectLoginHelper();

		$accessToken = $helper->getAccessToken();

		$logoutUrl = $helper->getLogoutUrl($accessToken, $back_url);

		HTTP::redirect($logoutUrl);

		return FALSE;
	}

	public function service_user_info()
	{
		try
		{
			return $this->api()->get('/me')->getGraphObject()->asArray();
		}
		catch (FacebookApiException $exception) {}
	}

	public function service_uid()
	{
		return $this->api()->getCanvasHelper()->getSignedRequest()->getUserId();
	}

} // End Auth Jam
