<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests ManyToMany fields.
 *
 * @package Jam-Auth
 * @group   jam
 * @group   jam.auth
 * @group   jam.auth.model
 * @group   jam.auth.model.user_token
 */
class Auth_Model_UserTokenTest extends Unittest_Auth_TestCase {

	/**
	 * Provider for test_find
	 */
	public function provider_find()
	{
		return array(
			array(1, TRUE),
			array(2, TRUE),
			// Expired tokens should be cleared up
			array(3, FALSE),
		);
	}
	
	/**
	 * 
	 * @dataProvider  provider_find
	 */
	public function test_find($id, $is_loaded)
	{
		$token = Jam::factory('test_user_token', $id);
		// Should now be a collection
		$this->assertInstanceOf('Model_Test_User_Token', $token);
		$this->assertEquals($is_loaded, $token->loaded(), 'Check if item is loaded');
	}

	/**
	 * Provider for test_find
	 */
	public function provider_get_token()
	{
		return array(
			array('4c14538f3c4a3b8cf30086958911d0d0ae1b2eb7', TRUE),
			array('f0dc12b77cf0214fbd04e2a224422f44adc7652c', TRUE),
			array('3add713f0577a68924b8dacc2058734165698f29', FALSE),
			// Expired tokens should be cleared up
			array('59ed73c1a3c105e7409c69c21770d674949c07e9', FALSE),
		);
	}

	/**
	 * @dataProvider provider_get_token
	 */
	public function test_get_token($token, $is_loaded)
	{
		$token = Jam::factory('test_user_token')->get_token($token);
		$this->assertEquals($is_loaded, $token->loaded());
	}

	public function test_delete_expired()
	{
		$this->assertEquals(3, Jam::query('test_user_token')->count());

		Jam::factory('test_user_token')->delete_expired();

		$this->assertEquals(2, Jam::query('test_user_token')->count());
	}

	public function test_create_token()
	{
		$token = Jam::factory('test_user_token')->create_token(array('test_user_id' => 2, 'user_agent' => 'new agent'));

		$this->assertEquals(2, $token->user->id());
		$this->assertEquals('new agent', $token->user_agent);
	}

}

