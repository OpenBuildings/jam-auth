<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default auth role
 *
 * @package	   Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @author	   creatoro
 * @copyright  (c) 2011 creatoro
 * @license	   http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Model_Auth_Role extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('name')

			->fields(array(
				'id' => Jam::field('primary'),
				'name' => Jam::field('string'),
				'description' => Jam::field('string'),
			))

			->associations(array(
				'users' => Jam::association('manytomany'),
			))

			->validator('name', array('present' => TRUE, 'length' => array('minimum' => 4, 'maximum' => 32), 'unique' => TRUE))
			->validator('description', array('length' => array('maximum' => 255)));

	}
} // End Kohana_Model_Auth_Role