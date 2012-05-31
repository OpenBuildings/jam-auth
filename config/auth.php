<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'services' => array(
		'facebook' => array(
			'enabled' => FALSE,
			'auto_login' => FALSE,
			'create_user' => TRUE,
			// 'back_url' => '/',
			'auth' => array(
				'appId' => '',
				'secret' => ''
			)
		),
		'twitter' => array(
			'enabled' => FALSE,
			// 'back_url' => '/',
			'auth' => array(
				'consumer_key' => 'YOUR_CONSUMER_KEY',
				'consumer_secret' => 'YOUR_CONSUMER_SECRET',
			),
			'create_user' => TRUE,
		),
	),
);