<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_Role extends Kohana_Model_Role {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Unittest_Auth_Testcase::$database_connection);

		parent::initialize($meta);
		$meta->associations(array(
			'users' => Jam::association('manytomany', array(
				'foreign' => 'test_user.id', 
				'through' => array(
					'model' => 'test_roles_users',
					'fields' => array(
						'our' => 'test_role_id',
						'foreign' => 'test_user_id'
					)
				)
			)),
		));

	}
}