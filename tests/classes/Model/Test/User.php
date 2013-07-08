<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_User extends Model_Auth_User {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Kohana::TESTING);

		parent::initialize($meta);

		$meta->associations(array(
			'user_tokens' => Jam::association('hasmany', array('foreign_model' => 'test_user_token', 'foreign_key' => 'test_user_id')),
			'roles' => Jam::association('manytomany', array(
				'foreign_model' => 'test_role', 
				'join_table' => 'test_roles_users',
				'foreign_key' => 'test_role_id',
				'association_foreign_key' => 'test_user_id',
			)),
		));
	}
}