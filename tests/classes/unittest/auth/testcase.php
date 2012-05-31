<?php defined('SYSPATH') OR die('No direct script access.');

class Unittest_Auth_TestCase extends Unittest_Database_TestCase {

	static public $database_connection = 30;

	protected $environmentDefault = array(
		'Request::$client_ip' => '8.8.8.8',
		'Request::$user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.162 Safari/535.19',
		'auth.driver' => 'jam_test',
		'auth.session_type' => 'auth_test',
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
	);

	protected $auth;

	public function setUp()
	{
		$this->_database_connection = Unittest_Auth_Testcase::$database_connection;
		parent::setUp();
		$_COOKIE = array();
		$_SESSION = array();
		$this->auth = new Auth_Jam_Test(Kohana::$config->load('auth'));
	}


	/**
	 * Inserts default data into database.
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_IDataSet
	 * @uses    Kohana::find_file
	 */
	public function getDataSet()
	{
		$data_set = $this->createXMLDataSet(Kohana::find_file('tests/test_data/auth', 'test', 'xml'));
		$decorator = new PHPUnit_Extensions_Database_DataSet_ReplacementDataSet($data_set);
		$decorator->addFullReplacement('TOMORROW', strtotime("tomorrow"));
		$decorator->addFullReplacement('YESTERDAY', strtotime("yesterday"));
		$decorator->addFullReplacement('TODAY', strtotime("today"));
		$decorator->addFullReplacement('LAST WEEK', strtotime("-1 week"));
		$decorator->addFullReplacement('LAST MONTH', strtotime("-1 month"));
		return $decorator;
	}

} // End Kohana_Unittest_Jam_TestCase