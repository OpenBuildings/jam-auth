<?php 

$role_login = Jam::create('test_role', array(
	'name' => 'login',
	'description' => 'Login Permssion',
));

$role_admin = Jam::create('test_role', array(
	'name' => 'admin',
	'description' => 'Admin Permssion',
));

$user1 = Jam::create('test_user', array(
	'id' => '1',
	'email' => 'admin@example.com',
	'username' => 'admin',
	'password' => 'qweqwe',
	'logins' => '5',
	'last_login' => strtotime('last month'),
	'last_login_ip' => '10.20.10.1',
	'facebook_uid' => 'facebook-test',
	'roles' => array($role_login),
	'user_tokens' => array(
		array(
			'id' => 1,
			'user_agent' => '92b1e2f536fa11fa996731b98b219f837d4436c8',
			'token' => '4c14538f3c4a3b8cf30086958911d0d0ae1b2eb7',
			'created' => strtotime('last week'),
			'expires' => strtotime('tomorrow'),
		),
		array(
			'id' => 3,
			'user_agent' => '92b1e2f536fa11fa996731b98b219f837d4436c8',
			'token' => '59ed73c1a3c105e7409c69c21770d674949c07e9',
			'created' => strtotime('last month'),
			'expires' => strtotime('last week'),
		)
	)
));

$user2 = Jam::create('test_user', array(
	'id' => '2',
	'email' => 'user@example.com',
	'username' => 'user',
	'password' => 'qweqwe',
	'logins' => '20',
	'last_login' => strtotime('last week'),
	'last_login_ip' => '10.20.10.2',
	'roles' => array($role_login, $role_admin),
	'user_tokens' => array(
		array(
			'id' => 2,
			'user_agent' => '92b1e2f536fa11fa996731b98b219f837d4436c8',
			'token' => 'f0dc12b77cf0214fbd04e2a224422f44adc7652c',
			'created' => strtotime('last week'),
			'expires' => strtotime('tomorrow'),
		)
	)
));
