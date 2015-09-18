<?php

require_once __DIR__.'/../vendor/autoload.php';

Kohana::modules(array(
	'database' => MODPATH.'database',
	'auth'     => MODPATH.'auth',
	'jam'      => __DIR__.'/../modules/jam',
	'jam-auth' => __DIR__.'/..',
));

function test_autoload($class)
{
	$file = str_replace('_', '/', $class);

	if ($file = Kohana::find_file('tests/classes', $file))
	{
		require_once $file;
	}
}

spl_autoload_register('test_autoload');

Kohana::$config
	->load('database')
		->set(Kohana::TESTING, array(
			'type'       => 'PDO',
			'connection' => array(
				'dsn'        => 'mysql:host=localhost;charset=utf8;dbname=test-jam-auth',
				'hostname'   => 'localhost',
				'database'   => 'test-jam-auth',
				'username'   => 'root',
				'password'   => '',
				'persistent' => TRUE,
			),
			'table_prefix' => '',
			'identifier'   => '`',
			'charset'      => 'utf8',
			'caching'      => FALSE,
		));
Kohana::$config
	->load('auth')
		->set('session_type', 'Auth_Test')
		->set('session_key', 'auth_user')
		->set('hash_key', '11111')
		->set('services', array(
			'facebook' => array(
				'enabled' => FALSE,
				'auto_login' => TRUE,
				'create_user' => TRUE,
				'auth' => array(
					'appId' => 'k',
					'secret' => 's'
				)
			),
		));

Kohana::$environment = Kohana::TESTING;

foreach (Database::instance(Kohana::TESTING)->query(Database::SELECT, 'SHOW TABLES') as $table)
{
	$table = $table['Tables_in_test-jam-auth'];

	Database::instance(Kohana::TESTING)->query(NULL, "TRUNCATE `{$table}`");
}
require_once __DIR__.'/database/fixtures/data.php';
