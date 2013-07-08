<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam-Auth
 * @group   jam-auth
 * @group   jam-auth.jam
 */
class Auth_JamTest extends Testcase_Auth {

	public function test_services_loaded()
	{
		$this->assertCount(1, $this->auth->services(), 'Should load one service (facebook)');	
		$this->assertArrayHasKey('facebook', $this->auth->services(), 'Should load the facebook service');
		$this->assertInstanceOf('Auth_Service_Facebook', $this->auth->services('facebook'), 'Should load the facebook service class');
	}

	public function test_logged_in()
	{
		$user = Jam::find('test_user', 1);
		$this->auth->force_login($user);
		
		$this->assertTrue($this->auth->logged_in(), 'should be logged in after complete login');
	}

	public function test_session()
	{
		$session = $this->auth->session();
		$this->assertInstanceOf('Session', $session);

		$this->assertSame($session, $this->auth->session(), 'Should cache the sesison object');
	}

	public function provider_login_method()
	{
		$user = Jam::find('test_user', 1);

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
	 * @dataProvider provider_login_method
	 */
	public function test_login_method($login_args, $should_be_logged_in)
	{
		$user = Jam::find('test_user', 1);
		if ($login_args[0])
		{
			$login_args[0] = Jam::find('test_user', 1)->get($login_args[0]);
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
		$user = Jam::find('test_user', 1);

		$token = $this->auth->remember($user);

		$jam_token = Jam::all('test_user_token')->valid_token($token->token)->first();
		
		$this->assertNotNull($jam_token);

		$this->auth->login_with_token($token);

		$this->assertTrue($this->auth->logged_in(), 'should be logged in after complete login');
	}

	public function provider_login_with_token()
	{
		$user = Jam::find('test_user', 1);

		return array(
			array(1, '+2 days', TRUE),
			array(2, '+2 days', TRUE),
			array(3, 'last week', FALSE)
		);
	}

	/**
	 * @dataProvider provider_login_with_token
	 */
	public function test_login_with_token($token_id, $expires, $should_be_logged_in)
	{
		$token = Jam::find('test_user_token', $token_id)->set('expires', strtotime($expires))->save();

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
			->will($this->returnValue(Jam::find('test_user', 1)));

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
		$user = Jam::find('test_user', 1);

		$this->auth->force_login($user);

		$this->assertTrue($this->auth->check_password('qweqwe'));
		$this->assertFalse($this->auth->check_password('test'));
	}

	public function data_access()
	{
		return array(
			array('public', 'index', 'public'),
			array('private', 'index', 'private'),
			array(array('public'), 'index', 'public'),
			array(array('public', 'except' => array('edit')), 'index', 'public'),
			array(array('public', 'except' => 'edit'), 'index', 'public'),
			array(array('public', 'except' => 'index'), 'index', 'private'),
			array(array('private', 'except' => array('edit')), 'index', 'private'),
			array(array('public', 'except' => array('edit', 'index')), 'index', 'private'),
			array(array('private', 'except' => array('edit', 'index')), 'index', 'public'),
			array(array('public', 'only' => 'index'), 'index', 'public'),
			array(array('public', 'only' => 'edit'), 'index', 'private'),
			array(array('public', 'only' => array('edit', 'create')), 'index', 'private'),
			array(array('public', 'only' => array('edit', 'index')), 'index', 'public'),
			array(array('public', 'except' => 'index', 'only' => array('edit', 'index')), 'index', 'private'),
			array(array('public', 'except' => 'create', 'only' => array('edit', 'index')), 'index', 'public'),
		);
	}
	
	/**
	 * @dataProvider data_access
	 */
	public function test_access($access, $action, $expected_access)
	{
		$this->assertEquals($expected_access, Auth_Jam::access($action, $access));
	}
	
}

