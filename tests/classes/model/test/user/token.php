<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_User_Token extends Model_Auth_User_Token {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Unittest_Auth_Testcase::$database_connection);

		parent::initialize($meta);
		$meta->associations(array(
			'user' => Jam::association('belongsto', array('foreign' => 'test_user', 'column' => 'test_user_id')),
		));

	}
}