<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default auth role
 *
 * @package	   Kohana/Auth
 * @author	   creatoro
 * @copyright  (c) 2011 creatoro
 * @license	   http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
class Kohana_Model_Role extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		// Fields defined by the model
		$meta->fields(array(
			'id' => Jam::field('primary'),
			'name' => Jam::field('string', array(
				'rules' => array(
					array('not_empty'),
					array('min_length', array(':value', 4)),
					array('max_length', array(':value', 32)),
				),
				'unique' => TRUE,
			)),
			'description' => Jam::field('string', array(
				'rules' => array(
					array('max_length', array(':value', 255)),
				),
			)),
		));

		$meta->associations(array(
			'users' => Jam::association('manytomany'),
		));
	}
} // End Auth Role Model