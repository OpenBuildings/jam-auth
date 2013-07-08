<?php defined('SYSPATH') OR die('No direct script access.');

class Testcase_Auth extends PHPUnit_Framework_TestCase {

	protected $auth;

	public function setUp()
	{
		parent::setUp();

		$_COOKIE = array();
		$_SESSION = array();
		Request::$client_ip = '8.8.8.8';
		Request::$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.162 Safari/535.19';

		$this->auth = new Auth_Jam_Test(Kohana::$config->load('auth'));
		$this->auth->session()->restart();

		Database::instance(Kohana::TESTING)->begin();
	}

	public function tearDown()
	{
		Database::instance(Kohana::TESTING)->rollback();
		parent::tearDown();
	}
} 
