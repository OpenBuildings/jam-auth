<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Add support for twitter usernames to Weblink field
 *
 * @package    Jam
 * @category   Fields
 * @author     Haralan Dobrev
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Jam_Field_Twitter extends Jam_Field_Weblink {

	/**
	 * If just a Twitter username is provided prefix it with 'http://twitter.com'.
	 * If username is provided with a '@' prefix it's stripped.
	 *
	 * @param  Jam_Model $model
	 * @param  string $value
	 * @param  boolean $loaded
	 * @return string
	 */
	public function set(Jam_Validated $model, $value, $is_changed)
	{
		if ($value AND strpos($value, 'twitter.com') === FALSE)
		{
			if (substr($value, 0, 1) === '@')
			{
				$value = substr($value, 1);
			}
			$value = 'http://twitter.com/'.$value;
		}
		return parent::set($model, $value, $is_changed);
	}

} // End Jam_Field_Twitter
