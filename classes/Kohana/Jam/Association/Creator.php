<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Handles belonsto associations for creator
 *
 * @package    Jam
 * @category   Behavior
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Jam_Association_Creator extends Jam_Association_Belongsto {

	public $foreign_model = 'user';
	public $required = TRUE;

	static public $current_user = NULL;

	static public function current($creator = NULL)
	{
		if ($creator !== NULL)
		{
			Jam_Association_Creator::$current_user = is_object($creator) ? $creator->id() : (int) $creator;			
		}
		return Jam_Association_Creator::$current_user;
	}

	public function initialize(Jam_Meta $meta, $name)
	{
		parent::initialize($meta, $name);
		
		if ($this->required)
		{
			$meta->validator($name, array('present' => TRUE));
		}
	}

	public function get(Jam_Validated $model, $value, $is_changed)
	{
		if ( ! $is_changed AND ! $value AND ! $model->loaded() AND Jam_Association_Creator::current())
		{
			$value = Jam::find($this->foreign_model, Jam_Association_Creator::current());
			$is_changed = TRUE;
			if ($this->inverse_of)
			{
				$value->{$this->inverse_of} = $model;
			}
		}

		return parent::get($model, $value, $is_changed);
	}

	public function model_before_check(Jam_Validated $model, $value, $changed)
	{
		if ( ! $model->loaded() AND ! isset($changed[$this->name]) AND Jam_Association_Creator::current())
		{
			$model->{$this->name} = Jam::find($this->foreign_model, Jam_Association_Creator::current());
		}
	}

} // End Jam_Association_Creator
