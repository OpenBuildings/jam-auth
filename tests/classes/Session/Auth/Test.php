<?php defined('SYSPATH') OR die('No direct script access.');

class Session_Auth_Test extends Session {

	/**
	 * @return  string
	 */
	public function id()
	{
		return 'session';
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param   string   variable name
	 * @param   mixed    default value to return
	 * @return  mixed
	 */
	// public function get($key, $default = NULL)
	// {
	// 	$data = parent::get($key, $default);
	// 	if ($data === FALSE OR $data === NULL)
	// 		return $data;

	// 	if ($data === 'b:0;')
	// 		return FALSE;
		
	// 	$old_error_reporting = error_reporting(error_reporting() ^ E_NOTICE);
		
	// 	$raw_data = unserialize($data);
		
	// 	if ($raw_data === FALSE)
	// 	{
	// 		$raw_data = $data;
	// 	}
		
	// 	error_reporting($old_error_reporting);

	// 	return $raw_data;
	// }

	/**
	 * Set a variable in the session array.
	 *
	 *     $session->set('foo', 'bar');
	 *
	 * @param   string   variable name
	 * @param   mixed    value
	 * @return  $this
	 */
	// public function set($key, $value)
	// {
	// 	return parent::set($key, serialize($value));
	// }

	/**
	 * @param   string  $id  session id
	 * @return  null
	 */
	protected function _read($id = NULL)
	{
		$_SESSION = array();
		$this->_data =& $_SESSION;

		return NULL;
	}

	/**
	 * @return  string
	 */
	protected function _regenerate()
	{
		return 'session';
	}

	/**
	 * @return  bool
	 */
	protected function _write()
	{
		$this->_data =& $_SESSION;
		
		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		$_SESSION = array();
		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _destroy()
	{
		$_SESSION = array();

		return TRUE;
	}

} // End Session_Native
