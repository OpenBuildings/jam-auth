<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam-Auth
 * @group   jam-auth
 * @group   jam-auth.model
 * @group   jam-auth.model.user_token
 */
class Auth_Model_UserTokenTest extends PHPUnit_Framework_TestCase {

	
	public function test_find_by_token()
	{
		$current_time = time();
		$expected_sql = "SELECT `test_user_tokens`.* FROM `test_user_tokens` WHERE `test_user_tokens`.`token` = '59ed73c1a3c105e7409c69c21770d674949c07e9' AND `test_user_tokens`.`expires` >= {$current_time}";
		$sql = (string) Jam::all('test_user_token')->valid_token('59ed73c1a3c105e7409c69c21770d674949c07e9', $current_time);

		$this->assertEquals($expected_sql, $sql);
	}

	public function test_expired()
	{
		$current_time = time();
		$expected_sql = "DELETE FROM `test_user_tokens` WHERE `test_user_tokens`.`expires` < {$current_time}";
		$sql = (string) Jam::delete('test_user_token')->expired(TRUE, $current_time);

		$this->assertEquals($expected_sql, $sql);
	}

	public function test_expires()
	{
		$tomorrow = time('tomorrow');
		$token = Jam::build('test_user_token', array('expires' => '+2 months'));

		$this->assertGreaterThan($tomorrow, $token->expires);
	}

}

