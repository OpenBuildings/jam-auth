<?php defined('SYSPATH') OR die('No direct script access.');

class Testcase_Functest_Auth extends Testcase_Functest {

	protected $auth;

	public function setUp()
	{
		parent::setUp();
		$this->environment()->backup_and_set(array(
			'_COOKIE' => array(),
			'_SESSION' => array(),
			'Request::$client_ip' => '8.8.8.8',
			'Request::$user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.162 Safari/535.19',
			'auth.driver' => 'Jam_Test',
			'auth.session_type' => 'Auth_Test',
			'auth.session_key' => 'auth_user',
			'auth.services' => array(
				'facebook' => array(
					'enabled' => FALSE,
					'auto_login' => TRUE,
					'create_user' => TRUE,
					'auth' => array(
						'appId' => 'k',
						'secret' => 's'
					)
				),
			),
		));

		$this->auth = new Auth_Jam_Test(Kohana::$config->load('auth'));
		$this->auth->session()->restart();
	}
} 