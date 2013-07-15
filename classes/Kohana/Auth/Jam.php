<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Jam Auth driver.
 *
 * @package    Kohana/Auth
 * @author     Ivan Kerin
 * @copyright  (c) 2012 Despark Ltd.
 * @license    http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */
abstract class Kohana_Auth_Jam extends Auth {

	protected $_services = array();

	public static function clear_cache()
	{
		Auth_Jam::$_instance = NULL;
	}

	public static function access($action, $access = array())
	{
		$access = (array) $access;
		$default_access = Arr::get($access, 0, 'public');
		$opposite_access = $default_access === 'public' ? 'private' : 'public';

		$except_actions = (array) Arr::get($access, 'except', array());
		$only_actions = (array) Arr::get($access, 'only', array());

		return (($except_actions AND in_array($action, $except_actions)) OR ( $only_actions AND ! in_array($action, $only_actions))) ? $opposite_access : $default_access;
	}
	
	public function __construct($config = array())
	{
		parent::__construct($config);

		foreach ($config['services'] as $service => $config) 
		{
			$class = 'Auth_Service_'.Jam::capitalize_class_name($service);
			$this->_services[$service] = new $class($config);
		}
	}

	/**
	 * Get all the available services, or only one service if provided a name
	 * @param  string $name the name of the service, e.g. 'facebook'
	 * @return array|Auth_Service
	 */
	public function services($name = NULL)
	{
		return $name === NULL ? $this->_services : Arr::get($this->_services, $name);
	}

	/**
	 * Checks if a session is active.
	 *
	 * @param   mixed    $role Role name string, role Jam object, or array with role names
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		// Get the user from the session
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;

		if ($user instanceof Model_Auth_User AND $user->loaded())
		{
			// If we don't have a roll no further checking is needed
			if ( ! $role)
				return TRUE;

			if (is_array($role))
			{
				return ! array_diff($role, $user->roles->as_array(NULL, 'name'));
			}
			elseif (is_string($role) OR $role instanceof Model_Auth_Role)
			{
				return $user->roles->has($role);	
			}
			else
			{
				throw new Kohana_Exception('Invalid Role ":role"', array("role" => (string) $role));
			}
		}
		return FALSE;
	}

	/**
	 * Getter
	 * The session instance for the configured session_type
	 * @return Session 
	 */
	public function session()
	{
		if ( ! $this->_session)
		{
			$this->_session = Session::instance($this->_config['session_type']);
		}
		return $this->_session;
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   $username  username
	 * @param   string   $password  password
	 * @param   boolean  $remember  enable autologin
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)
	{
		if (is_string($password))
		{
			// Create a hashed password
			$password = $this->hash($password);
		}

		$user = $this->_load_user($user);

		// If the passwords match, perform a login
		if ($user AND $user->roles->has('login') AND $user->password === $password)
		{
			if ($remember === TRUE)
			{
				$this->remember($user);
			}

			// Finish the login
			$this->complete_login($user);

			return TRUE;
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Create autologin token
	 * @param  Model_User $user 
	 * @return Model_User_Token
	 */
	public function remember($user)
	{
		$token = $user->build_user_token(array('user_agent' => sha1(Request::$user_agent)))->save();

		// Set the autologin cookie
		$this->_autologin_cookie($token->token, $this->_config['lifetime']);

		return $token;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    $user                    username string, or user Jam object
	 * @param   boolean  $mark_session_as_forced  mark the session as forced
	 * @param   boolean  $remember                force to remeber the user
	 * @return  boolean
	 */
	public function force_login($user, $mark_session_as_forced = FALSE, $remember = FALSE)
	{
		$user = $this->_load_user($user);

		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->session()->set('auth_forced', TRUE);
		}

		if ($remember)
		{
			$this->remember($user);
		}

		// Run the standard completion
		return $this->complete_login($user);
	}

	/**
	 * login using a spesific token
	 * @param  string $token token hash
	 * @return Model_User|NULL
	 */
	public function login_with_token($token)
	{
		if ( ! $token)
			return NULL;

		// Load the token and user
		$token = $this->_load_token($token);

		if ($token AND $token->user AND $token->user->loaded())
		{
			if ( ! $token->user_agent OR $token->user_agent === sha1(Request::$user_agent))
			{
				// Save the token to create a new unique token
				$token->save();

				// Set the new token
				$this->_autologin_cookie($token->token, $token->expires - time());

				// Complete the login with the found data
				$this->complete_login($token->user);

				// Automatic login was successful
				return $token->user;
			}

			// Token is invalid
			$token->delete();
		}
	}

	/**
	 * Attempt to login the user using specific service
	 * @param  string  $name     the name of the service
	 * @param  boolean $remember create autologin token
	 * @return Model_User|NULL
	 */
	public function login_with_service($name, $remember = FALSE)
	{
		if ($user = $this->services($name)->login())
		{	
			if ($remember === TRUE)
			{
				$this->remember($user);
			}
			
			$this->complete_login($user);
			return $user;
		}

		return FALSE;
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  mixed
	 */
	public function auto_login()
	{
		if ($token = $this->_autologin_cookie())
		{
			if ($user = $this->login_with_token($token))
				return $user;
		}

		if (Request::current() AND $token = Arr::get(Request::current()->query(), '_token'))
		{
			if ($user = $this->login_with_token($token))
				return $user;
		}

		foreach ($this->services() as $service) 
		{
			if ($service->auto_login_enabled() AND $user = $service->get_user())
			{
				// $this->remember($user);
				$this->complete_login($user);
				return $user;
			}
		}

		return FALSE;
	}

	/**
	 * Gets the currently logged in user from the session (with auto_login check).
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user($default = NULL)
	{
		// Load the session for the parent method
		$this->session();
		$user = $this->_load_user(parent::get_user($default));

		if ( ! ($user AND $user->loaded()))
		{
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
	}

	protected function _load_user($user)
	{
		if ( ! $user)
			return NULL;
		
		return is_object($user) ? $user : Jam::find('user', $user);
	}

	protected function _load_token($token)
	{
		if ( ! $token)
			return NULL;

		return is_object($token) ? $token : Jam::all('user_token')->valid_token($token)->first();
	}

	/**
	 * Getter / Setter of the autologin cookie
	 * Extend this method in your tests so you can remove dependance on cookies there
	 * 
	 * @param  string $token   
	 * @param  integer $expires days lifetime
	 * @return mixed
	 */
	protected function _autologin_cookie($token = NULL, $expires = NULL)
	{
		if ($token === FALSE)
		{
			Cookie::delete('authautologin');
		}
		elseif ($token !== NULL) 
		{
			Cookie::set('authautologin', $token, $expires);
		}
		else
		{
			return Cookie::get('authautologin');
		}
		return $this;
	}

	/**
	 * Log a user out and remove any autologin cookies, and goes to each service to log out the user there
	 *
	 * @param   boolean  $destroy     completely destroy the session
	 * @param	boolean  $logout_all  remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		// Set by force_login()
		$this->session()->delete('auth_forced');

		if ($token = $this->_autologin_cookie())
		{
			// Delete the autologin cookie to prevent re-login
			$this->_autologin_cookie(FALSE);

			// Clear the autologin token from the database
			$token = $this->_load_token($token);

			if ($token AND $token->loaded() AND $token->user)
			{
				if ($logout_all)
				{
					$token->user->user_tokens->clear();
				}
				$token->delete();
			}
		}

		foreach ($this->services() as $service) 
		{
			if ($user = $service->get_user())
			{
				$service->logout();
			}
		}

		return parent::logout($destroy);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   $user  username string, or user Jam object
	 * @return  string
	 */
	public function password($user)
	{
		return $this->_load_user($user)->password;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles.
	 *
	 * @param   object  $user Jam object
	 * @return  void
	 */
	protected function complete_login($user)
	{
		$user->last_login_ip = Request::$client_ip;
    
		$user->complete_login();
		
		// Regenerate session_id
		$this->session()->regenerate();

		// Store username in session
		$this->session()->set($this->_config['session_key'], $user->id);

		return TRUE;
	}

	/**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;

		return ($this->hash($password) === $user->password);
	}

} // End Auth Jam