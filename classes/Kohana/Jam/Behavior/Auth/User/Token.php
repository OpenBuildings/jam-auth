<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    Jam
 * @category   Behavior
 * @author     Ivan Kerin
 * @copyright  (c) 2011-2012 Despark Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Kohana_Jam_Behavior_Auth_User_Token extends Jam_Behavior {

	public function builder_call_valid_token(Database_Query $query, Jam_Event_Data $data, $token, $current_time = NULL)
	{
		$query
			->where('token', '=', $token)
			->where('expires', '>=', ($current_time === NULL) ? time() : $current_time);
	}

	public function builder_call_expired(Database_Query $query, Jam_Event_Data $data, $token = TRUE, $current_time = NULL)
	{
		$query->where('expires', (bool) $token ? '<' : '>=', ($current_time === NULL) ? time() : $current_time);
	}
}