<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam-Auth
 * @group   jam-auth
 * @group   jam-auth.service
 */
class Auth_Jam_ServiceTest extends Testcase_Auth {

	public function test_api()
	{
		$facebook = $this
			->getMockBuilder('Facebook\Facebook')
			->disableOriginalConstructor()
			->getMock();

		$service = new Auth_Service_Facebook_Test(array());

		$service->api($facebook);

		$this->assertSame($facebook, $service->api(), 'Should get the api');
	}

	public function test_type()
	{
		$service = new Auth_Service_Facebook_Test(array());
		$this->assertEquals('facebook', $service->type());
	}

	public function test_enabled()
	{
		$service = new Auth_Service_Facebook_Test(array('enabled' => TRUE));
		$this->assertTrue($service->enabled());

		$service = new Auth_Service_Facebook_Test(array('enabled' => FALSE));
		$this->assertFalse($service->enabled());
	}

	public function test_build_user()
	{
		$facebook = $this
			->getMockBuilder('Facebook\Facebook')
			->setMethods(['getCanvasHelper'])
			->disableOriginalConstructor()
			->getMock();

		$canvas = $this
			->getMockBuilder('Facebook\FacebookCanvasHelper')
			->setMethods(['getSignedRequest'])
			->disableOriginalConstructor()
			->getMock();

		$signedRequest = $this
			->getMockBuilder('Facebook\SignedRequest')
			->setMethods(['getUserId'])
			->disableOriginalConstructor()
			->getMock();

		$facebook
			->method('getCanvasHelper')
			->willReturn($canvas);

		$canvas
			->method('getSignedRequest')
			->willReturn($signedRequest);

		$signedRequest
			->method('getUserId')
			->willReturn('facebook-test');

		$service = new Auth_Service_Facebook_Test(array('enabled' => TRUE));
		$service->api($facebook);

		$user = $service->build_user(array('data' => 'data'), TRUE);
		$this->assertNotNull($user);
		$this->assertTrue($user->roles->has('login'), 'Should assign login role');
		$this->assertEquals('facebook-test', $user->facebook_uid, 'Should populate service id field');
	}

	public function test_get_user()
	{
		$facebook = $this
			->getMockBuilder('Facebook\Facebook')
			->setMethods(['get', 'getCanvasHelper'])
			->disableOriginalConstructor()
			->getMock();

		$response = $this
			->getMockBuilder('Facebook\FacebookResponse')
			->setMethods(['getGraphObject'])
			->disableOriginalConstructor()
			->getMock();

		$object = $this
			->getMockBuilder('Facebook\GraphNodes\GraphObject')
			->setMethods(['asArray'])
			->disableOriginalConstructor()
			->getMock();

		$canvas = $this
			->getMockBuilder('Facebook\FacebookCanvasHelper')
			->setMethods(['getSignedRequest'])
			->disableOriginalConstructor()
			->getMock();

		$signedRequest = $this
			->getMockBuilder('Facebook\SignedRequest')
			->setMethods(['getUserId'])
			->disableOriginalConstructor()
			->getMock();

		$facebook
			->method('get')
			->with($this->equalTo('/me'))
			->willReturn($response);

		$response
			->method('getGraphObject')
			->willReturn($object);

		$object
			->method('asArray')
			->willReturn(['email' => 'admin@example.com']);

		$canvas
			->method('getSignedRequest')
			->willReturn($signedRequest);

		$facebook
			->method('getCanvasHelper')
			->willReturn($canvas);

		$signedRequest
			->method('getUserId')
			->willReturn('facebook-test');


		$service = new Auth_Service_Facebook_Test(array('enabled' => TRUE));
		$service->api($facebook);

		$user = $service->get_user();

		$this->assertEquals(1, $user->id());

		$user->set('facebook_uid', NULL)->save();

		$user = $service->get_user();

		$this->assertEquals(1, $user->id());
		$this->assertEquals('facebook-test', $user->facebook_uid);
	}
}
