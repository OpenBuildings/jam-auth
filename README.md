# Jam driver for Kohana Auth

This is a Kohana Auth driver, for Jam, however it adds some more functionality that is absent in Auth ORM module, namely the ability to login / logout with different services, such as twitter, facebook and google. The service infrastructure is there, however only the facebook service is implemented.

## Installation

Enable the module in your **bootstrap.php**:

	Kohana::modules(array(
		'jam-auth' => MODPATH.'jam-auth',
		// ...
	));

In your config/auth.php file:

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

## Model Extension for facebook

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

## Logging out

Logging out is a bit more complicated as it requires going to each service's site to logout from there so will have to go several times through the "logout" page to do that. This is handled automatically, but if you have explicit redirects, they might not work as expected.

License
-------

Jam-auth is Copyright © 2012 Despark Ltd. developed by Ivan Kerin. It is free software, and may be redistributed under the terms specified in the LICENSE file.


