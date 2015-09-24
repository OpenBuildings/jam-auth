<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    Jam
 * @category   Behavior
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Jam_Behavior_Username extends Jam_Behavior {

	public function initialize(Jam_Meta $meta, $name)
	{
		parent::initialize($meta, $name);

		$meta
			->unique_key(array($this, 'username_key'))

			->name_key('username')

			->fields([
				'username' => Jam::field('string'),
			])

			->validator('username', array(
				'length' => array('minimum' => 3, 'maximum' => 32),
				'present' => TRUE,
				'format' => array('regex' => '/^[a-zA-Z0-9\_\-]+$/')
			));
	}

	public function username_key($value)
	{
		return Valid::email($value) ? 'email' : ((is_numeric($value) OR $value === NULL) ? 'id' : 'username');
	}

}
