# Jam driver for Kohana Auth

This is a Kohana Auth driver, for Jam, however it adds some more functionality that is absent in Auth ORM module, namely the ability to login / logout with different services, such as Twitter, Facebook and Google. The service infrastructure is 
there, however only the Facebook service is implemented.

## Installation

Enable the module in your **bootstrap.php**:
	
	DOCROOT/bootstrap.php
	Kohana::modules(array(
		'jam-auth' => MODPATH.'jam-auth',
		// ...
	));

	APPPATH/config/auth.php
	return array(
		'driver' => 'jam'

		// In order to use facebook auth service
		'services' => array(
			'facebook' => array(

				'enabled' => TRUE,

				// Automatically check this service for login credentials if the user is not logged in
				'auto_login' => FALSE,

				// Attempt to create the user if he's logged in with the service, but the user does not yet exist
				'create_user' => TRUE,

				// Facebook login credentials
				'auth' => array(
					'appId' => '224919274252280',
					'secret' => '4c891b816f1273442bdd7e1bac33f7e3'
				), 
			),
		),
	);

You will need to create the database tables - there are 2 sql files provided for mysql / postgre

* auth-schema-mysql.sql 
* auth-schema-postgresql.sql

You can execute this to create your user, role and and token tables

## Getting started

The default Model_User has some fields set up for you:

* __id__ - primary key
* __email__ - string field with validation for email, unique
* __username__ - string field with validation for username (letters, dashes and underscores), length 3..32, required
* __password__ - will autohash with the hash method on save (so before the save you can retrieve the actual password when set)
* __logins__ - how many times the user has logged in
* __last_login__ - the date of the last login
* __last_login_ip__ - the IP from which the user performed the last login
* __facbook_uid__ - this is used by the facebook auth service
* __twitter_uid__ - this is used by the twitter auth service

You can use the model "as is" but you most probably will want to customize it, so create a file Model_User in your APPPATH/classes/model/ folder.

	APPPATH/classes/model/user.php
	<?php defined('SYSPATH') OR die('No direct script access.');

	class Model_User extends Model_Auth_User {

		public static function initialize(Jam_Meta $meta)
		{
			// Initialize the parent - set fields and associations
			parent::initialize($meta);

			$meta->behaviors(array(
				'sluggable' => Jam::behavior('Sluggable'),
			));

			// Add additional fields
			$meta->fields(array(
				'first_name' => Jam::field('string'),
				'last_name'  => Jam::field('string'),
			));
		}
	}


To setup the login code you can use the ->login() method of the jam auth like this:

	APPPATH/views/session/form.php
	<?php if (isset($message)): ?>
		<div><?php echo $message ?></div>
	<?php endif ?>
	<form action="session/login">
		<input type="text" name="username"/>
		<input type="password" name="password"/>
		<input type="checkbox" value="1" name="remember"/>
	</form>

	APPPATH/classes/controller/session.php
	class Controller_Session extends Controller {

		function action_login()
		{
			$request = $this->request;
			$view = View::factory('session/form');

			if ($request->method() === Request::POST)
			{
				// If the username / password is correct, login the user and return it, otherwise return FALSE
				if ($user = Auth::instance()->login($request->post('username'), $request->post('password'), $request->post('remember')))
				{
					$view->set('message', 'You have successfully logged in '.$user->name());
				}
				else
				{
					$view->set('message', 'Username or password incorrect');
				}
			}

			$this->response->body($view);
		}
	}

## Auto login

Auto login uses cookies to login the user automatically.

	// By setting the "remember me" to TRUE, you create an auth token entry in the database with the corresponding cookie
	$user = Auth::instance()->login($username, $password, TRUE);

	// Which will be used by these methods to automatically login the user if the cookie matches the db entry
	Auth::instance()->logged_in();
	Auth::instance()->get_user();
	Auth::instance()->auto_login();

Services also can be configured to be used for auto-login, for example if Facebook auth service config has 'auto_login' => TRUE, then any ->auto_login() call will try to use the service to for logging in the user. Be careful with this however, as this can add high overhead to the requests of your non-logged in users.

It is best to use a separate method for logging in with each service as described in the following example:

## Login with service

Jam Auth also supports Facebook logins, and the Facebook API is included in the vendors folder. Example login with facebook code: 

	APPPATH/classes/controller/session.php
	class Controller_Session extends Controller {

		function action_login_facebook()
		{
			if ($user = $this->auth->login_with_service('facebook'))
			{
				$this->redirect(URL::site());
			}
			else
			{
				throw new Auth_Exception_Service('There was an error logging in through facebook');
			}
		}
	}

->login_with_service() is similar to ->login() and returns the logged in user on successful login and FALSE on failure.
You will have to login the user to the service yourself however. So the way this works for Facebook in particular is that you perform a "login" action with javascript, and return to this URL (/session/login_facebook). And then it finds out what user is this in your own database. You can control whether to create the user automatically if its not present in the database with the 'create_user' config option.


## Model Extension for Facebook

In order to load all the needed information for the user from the service (You want more information about the user than just the email) you can extend the load_service_values() method of the model and have specific code to handle extracting that information. For example:

	public function load_service_values(Auth_Service $service, array $user_data, $create = FALSE)
	{
		if ($service->name == 'facebook')
		{
			$email = Arr::get($user_data, 'email');
			
			$this->set(array_filter(array(
				'facebook_uid' => $user_data['id'],
				'username'     => URL::title(Arr::get($user_data, 'username', $user_data['name']), '-', TRUE),
				'first_name'   => Arr::get($user_data, 'first_name'),
				'last_name'    => Arr::get($user_data, 'last_name'),
				'image'        => 'http://graph.facebook.com/'.$user_data['id'].'/picture?type=large',
				'email'        => $email,
				'facebook'     => 'http://facebook.com/'.Arr::get($user_data, 'username', $user_data['id']),
			)));
		}

		return $this;
	}

## Forgotten password

Forgotten password is not implemented per se, but it is very easy to set up. 

	APPPATH/views/session/forgotten.php
	<?php if (isset($message)): ?>
		<div><?php echo $message ?></div>
	<?php endif ?>
	<form action="session/login">
		<input type="text" name="email"/>
	</form>


	APPPATH/classes/controller/session.php
	class Controller_Session extends Controller {

		function action_send_forgotten()
		{
			$request = $this->request;
			$view = View::factory('session/form');

			if ($request->method() === Request::POST)
			{
				$user = Jam::find('user', $request->post('email'));
				if ($user->loaded())
				{
					// Generate a special onetime token, that will expire in a week
					$token = $user->generate_login_token();
					mail($user->email, 'Forgotten Password', 'Click this link to login: '.URL::site(TRUE).'/session/login_token/'.$token->token);
					$view->set('message', 'An email with instructions has been sent to '.$user->email);
				}
				else
				{
					$view->set('message', 'A user with this email was not found');
				}
			}

			$this->response->body($view);
		}

		function action_login_token()
		{
			// Perform the actual login - if the login is successful - return the user, otherwise return FALSE
			if ($user = $this->auth->login_with_token($this->request->param('id')))
			{
				$this->redirect(URL::site());
			}
			else
			{
				throw new Auth_Exception_Service('The token has expired or was incorrect');
			}
		}
	}

## Logging out

Logging out is a bit more complicated as it requires going to each service's site to logout from there so will have to go several times through the "logout" page to do that. This is handled automatically, but if you have explicit redirects, they might not work as expected. Example logout code:

	APPPATH/classes/controller/session.php
	class Controller_Session extends Controller {

		function action_destroy()
		{
			$this->auth->logout();
			$this->redirect(URL::site());
		}
	}


License
-------

Jam-auth is Copyright © 2012-2013 OpenBuildings Ltd. developed by Ivan Kerin. It is free software, and may be redistributed under the terms specified in the LICENSE file.


