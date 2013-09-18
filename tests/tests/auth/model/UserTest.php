<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam-Auth
 * @group   jam-auth
 * @group   jam-auth.model
 * @group   jam-auth.model.user
 */
class Auth_Model_UserTest extends PHPUnit_Framework_TestCase {

	/**
	 * Provider for test_unique_key
	 */
	public function provider_unique_key()
	{
		return array(
			array(1, 'id'),
			array(NULL, 'id'),
			array('admin', 'username'),
			array('user', 'username'),
			array('login21', 'username'),
			array('admin@example.com', 'email'),
			array('user@example.com', 'email'),
			array('user@example23', 'username'),
		);
	}
	
	/**
	 * 
	 * @dataProvider  provider_unique_key
	 */
	public function test_unique_key($value, $expected_attribute)
	{
		$this->assertEquals($expected_attribute, Model_Auth_User::unique_key($value));
	}

	public function test_complete_login()
	{
		$user = $this->getMock('Model_Test_User', array('save'), array('test_user'));
		$user
			->expects($this->never())
			->method('save');

		$user->set(array('logins' => 3, 'last_login' => strtotime('-2 days')));

		$user->complete_login();

		$loaded_user = $this->getMock('Model_Test_User', array('save'), array('test_user'));
		$loaded_user
			->expects($this->once())
			->method('save');

		$loaded_user->load_fields(array('id' => 1, 'logins' => 3, 'last_login' => strtotime('-2 days')));

		$now = time();
		$loaded_user->complete_login();

		$this->assertEquals(4, $loaded_user->logins);
		$this->assertGreaterThanOrEqual($now, $loaded_user->last_login);
	}

	public function provider_validation()
	{
		return array(
			array('email', 'new@example.com', TRUE),
			array('email', 'example.com', FALSE),
			array('email', 'user.asd@example.com', TRUE),
			array('email', ' ', FALSE),
			array('username', 'new-user', TRUE),
			array('username', '', FALSE),
			array('username', 'sm', FALSE),
			array('username', 'hh345 345 -34', FALSE),
			array('username', 'very_long_userna_very_long_userna_', FALSE),
			array('password', 'new-password', TRUE),
			array('last_login_ip', '34802', FALSE),
			array('last_login_ip', '10.10.10', FALSE),
			array('last_login_ip', '10.0.0.1', TRUE),
		);
	}

	/**
	 * Test various validation conditions
	 * @dataProvider provider_validation
	 * @param  Model_Test_User  $user     
	 * @param  boolean $is_valid 
	 * @return NULL            
	 */
	public function test_validation($field, $value, $is_valid)
	{
		$user = Jam::build('test_user')
			->load_fields(array(
				'id' => '1',
				'email' => 'admin@example.com',
				'username' => 'admin',
				'last_login_ip' => '10.20.10.1',
			));

		$user->set($field, $value);

		$this->assertEquals($is_valid, $user->check(), 'The check should return '.($is_valid ? 'TRUE' : 'FALSE').', errors '.$user->errors());
	}

	public function provider_validate_password()
	{
		return array(
			array(array('password' => 'new-password', 'password_confirmation' => ''), FALSE),
			array(array('password' => 'new-password', 'password_confirmation' => 'old-password'), FALSE),
			array(array('password' => 'new-password', 'password_confirmation' => 'new-password'), TRUE),
		);
	}

	/**
	 * @dataProvider provider_validate_password
	 */
	public function test_validate_password($params, $is_valid)
	{
		$user = Jam::build('test_user')->load_fields(array(
			'id' => '1',
			'email' => 'admin@example.com',
			'username' => 'admin',
			'password' => '519b05d6ffcab58b7525cdea9c58a8fdb4584e3bd41427db1fcc20ef05dafad6',
			'last_login_ip' => '10.20.10.1',
		));

		$user->set($params);

		$user->validate_password = TRUE;

		$this->assertEquals($is_valid, $user->check(), 'The check should return '.($is_valid ? 'TRUE' : 'FALSE').', errors '.$user->errors());
	}

	public function test_build_user_token()
	{
		$user = Jam::build('test_user')->load_fields(array(
			'id' => '1',
			'email' => 'admin@example.com',
			'username' => 'admin',
		));

		$now = time();

		$token = $user->build_user_token();

		$this->assertGreaterThan($now, $token->expires);
		$this->assertNotNull($token->token);
	}
}

