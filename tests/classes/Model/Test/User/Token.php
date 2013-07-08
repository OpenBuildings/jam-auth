<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_User_Token extends Model_Auth_User_Token {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Kohana::TESTING);

		parent::initialize($meta);
		
		$meta->associations(array(
			'user' => Jam::association('belongsto', array('foreign_model' => 'test_user', 'foreign_key' => 'test_user_id')),
		));

	}
}