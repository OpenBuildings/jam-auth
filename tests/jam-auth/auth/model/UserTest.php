<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests ManyToMany fields.
 *
 * @package Jam-Auth
 * @group   jam
 * @group   jam.auth
 * @group   jam.auth.model
 * @group   jam.auth.model.user
 */
class Auth_Model_UserTest extends Unittest_Auth_TestCase {

	/**
	 * Provider for test_find
	 */
	public function provider_find()
	{
		return array(
			array(1, 1),
			array(555, NULL),
			array('admin', 1),
			array('user', 2),
			array('login21', NULL),
			array('admin@example.com', 1),
			array('user@example.com', 2),
			array('user@example23', NULL),
		);
	}
	
	/**
	 * 
	 * @dataProvider  provider_find
	 */
	public function test_find($key, $id)
	{
		$builder = Jam::query('test_user', $key);
		$this->assertInstanceOf('Model_Builder_Auth_User', $builder);
		
		// Select the result
		$result = $builder->find();
		
		// Should now be a collection
		$this->assertInstanceOf('Model_Test_User', $result);
		if ($id)
		{
			$this->assertTrue($result->loaded(), 'Check if item is loaded');
		}
		$this->assertEquals($id, $result->id(), 'Check if the loaded item is the correct one');
	}

	public function test_complete_login()
	{
		$user = Jam::factory('test_user', 1);
		$now = time();
		$user->complete_login();

		$this->assertEquals(6, $user->logins, 'Should increment logins count');
		$this->assertGreaterThanOrEqual($now, $user->last_login, "Should update last login to now");
	}

	public function test_save_with_expired_tokens()
	{
		$user = Jam::factory('test_user', 1);
		foreach ($user->user_tokens as $i =>$token) 
		{
			// GO through the tokens to trigger its auto delete mechanizm
			$token->loaded();
			// Assign back a token, maybe its deleted
			$user->user_tokens[$i] = $token;
		}
		$user->complete_login();
		$user->save();
		
	}

	public function provider_validation()
	{
		return array(
			array('email', 'new@example.com', TRUE),
			array('email', 'example.com', FALSE),
			array('email', 'user@example.com', FALSE),
			array('email', NULL, FALSE),
			array('username', 'new-user', TRUE),
			array('username', '', FALSE),
			array('username', 'sm', FALSE),
			array('username', 'hh345 345 -34', FALSE),
			array('username', 'very_long_userna_very_long_userna_', FALSE),
			array('password', 'sm', FALSE),
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
		$user = Jam::factory('test_user', 1);
		$user->set($field, $value);

		$this->assertEquals($is_valid, $user->check(), "The check should return $is_valid");
	}

	public function provider_add_password_validation()
	{
		return array(
			array(array('password' => 'new-password'), FALSE),
			array(array('password' => 'new-password', 'password_confirm' => 'old-password'), FALSE),
			array(array('password' => 'new-password', 'password_confirm' => 'new-password'), TRUE),
		);
	}

	/**
	 * @dataProvider provider_add_password_validation
	 */
	public function test_add_password_validation($params, $is_valid)
	{
		Jam::clear_cache('test_user');
		Jam::meta('test_user')->add_password_validation();
		$user = Jam::factory('test_user', 1)->set($params);

		$this->assertEquals($is_valid, $user->check(), "The check should return $is_valid ".print_r($user->errors(), TRUE));
	}

	public function test_generate_login_token()
	{
		$user = Jam::factory('test_user', 1);
		$token = $user->generate_login_token();
		$this->assertInstanceOf('Model_Test_User_Token', $token);

		$retrieved_token = Jam::factory('test_user_token')->get_token($token->token);

		$this->assertTrue($retrieved_token->loaded());
		$this->assertEquals($retrieved_token->id, $token->id());
	}


}

