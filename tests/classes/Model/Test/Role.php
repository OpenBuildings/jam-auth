<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_Role extends Model_Auth_Role {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Kohana::TESTING);

		parent::initialize($meta);
		$meta->associations(array(
			'users' => Jam::association('manytomany', array(
				'foreign_model' => 'test_user', 
				'join_table' => 'test_roles_users',
				'foreign_key' => 'test_user_id',
				'foreign_association_key' => 'test_role_id',
			)),
		));

	}
}