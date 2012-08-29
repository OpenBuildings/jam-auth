<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests ManyToMany fields.
 *
 * @package Jam-Auth
 * @group   jam
 * @group   jam.auth
 * @group   jam.auth.jam
 */
class Auth_JamTest extends Unittest_Auth_TestCase {

	public function test_services_loaded()
	{
		$this->assertCount(1, $this->auth->services(), 'Should load one service (facebook)');	
		$this->assertArrayHasKey('facebook', $this->auth->services(), 'Should load the facebook service');
		$this->assertInstanceOf('Auth_Service_Facebook', $this->auth->services('facebook'), 'Should load the facebook service class');
	}

	public function test_logged_in()
	{
		$user = Jam::factory('test_user', 1);
		$this->auth->force_login($user);
		
		$this->assertTrue($this->auth->logged_in(), 'should be logged in after complete login');
	}

	public function test_session()
	{
		$session = $this->auth->session();
		$this->assertInstanceOf('Session', $session);

		$this->assertSame($session, $this->auth->session(), 'Should cache the sesison object');
	}

	public function provider_login()
	{
		$user = Jam::factory('test_user', 1);

		return array(
			array(array(NULL, 'qweqwe'), TRUE),
			array(array('username', 'qweqwe'), TRUE),
			array(array('email', 'qweqwe'), TRUE),
			array(array(NULL, 'qweqwe2'), FALSE),
			array(array('username', 'qweqwe2'), FALSE),
			array(array('email', 'qweqwe2'), FALSE),
			array(array('wrong', 'qweqwe'), FALSE),
		);
	}
	/**
	 * @dataProvider provider_login
	 */
	public function test_login($login_args, $should_be_logged_in)
	{
		$user = Jam::factory('test_user', 1);
		if ($login_args[0])
		{
			$login_args[0] = Jam::factory('test_user', 1)->get($login_args[0]);
		}
		else
		{
			$login_args[0] = $user;
		}

		call_user_func_array(array($this->auth, 'login'), $login_args);

		$this->assertEquals($should_be_logged_in, $this->auth->logged_in());

		// Cleanup session
		$this->auth->logout();
	}

	public function test_remember()
	{
		$user = Jam::factory('test_user', 1);

		$token = $this->auth->remember($user);
		
		$jam_token = Jam::factory('test_user_token')->get_token($token->token);
		
		$this->assertTrue($jam_token->loaded());

		$this->auth->login_with_token($token);

		$this->assertTrue($this->auth->logged_in(), 'should be logged in after complete login');
	}

	public function provider_login_with_token()
	{
		$user = Jam::factory('test_user', 1);

		return array(
			array(1, TRUE),
			array(2, TRUE),
			array(3, FALSE)
		);
	}

	/**
	 * @dataProvider provider_login_with_token
	 */
	public function test_login_with_token($token_id, $should_be_logged_in)
	{
		$token = Jam::factory('test_user_token', $token_id);

		$this->auth->login_with_token($token->token);

		$this->assertEquals($should_be_logged_in, $this->auth->logged_in());

		$this->auth->logout();
	}

	public function test_login_with_service()
	{
		$facebook = $this->getMock('Auth_Service_Facebook', array('login'));

		// First attempt fails
		$facebook
			->expects($this->at(0))
			->method('login')
			->will($this->returnValue(FALSE));

		// Second attempt succeeds
		$facebook
			->expects($this->at(1))
			->method('login')
			->will($this->returnValue(Jam::factory('test_user', 1)));

		$this->auth->set_service('facebook', $facebook);

		$this->auth->login_with_service('facebook');
		$this->assertFalse($this->auth->logged_in(), 'should not be logged in after facebook login FALSE');

		$this->auth->login_with_service('facebook');
		$this->assertTrue($this->auth->logged_in(), 'should not be logged in after facebook login TRUE');
	}

	public function test_auto_login()
	{
		$facebook = $this->getMock('Auth_Service_Facebook', array('get_user'), array(array('auto_login' => TRUE, 'enabled' => TRUE)));

		$this->auth->set_service('facebook', $facebook);

		$facebook
			->expects($this->once())
			->method('get_user')
			->will($this->returnValue(FALSE));

		// Should fire upt facebook's get_user method for login
		$this->auth->auto_login();
	}

	public function test_get_user()
	{
		$facebook = $this->getMock('Auth_Service_Facebook', array('get_user'), array(array('auto_login' => TRUE, 'enabled' => TRUE)));

		$this->auth->set_service('facebook', $facebook);

		$facebook
			->expects($this->once())
			->method('get_user')
			->will($this->returnValue(FALSE));

		// Should fire upt facebook's get_user method for login
		$this->auth->get_user();
	}

	public function test_check_password()
	{
		$user = Jam::factory('test_user', 1);

		$this->auth->force_login($user);

		$this->assertTrue($this->auth->check_password('qweqwe'));
		$this->assertFalse($this->auth->check_password('test'));
	}


}

