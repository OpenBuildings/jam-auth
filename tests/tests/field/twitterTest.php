<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam-Auth
 * @group   jam
 * @group   jam.field
 * @group   jam.field.twitter
 * @author Haralan Dobrev
 */
class Field_TwitterTest extends PHPUnit_Framework_TestCase {

	public function test_construction()
	{
		$this->assertInstanceOf('Jam_Field_Weblink', new Jam_Field_Twitter);
	}

	/**
	 * Provider for test_twitter_set
	 */
	public function provider_twitter()
	{
		return array(
			array('username', 'http://twitter.com/username'),
			array('@username', 'http://twitter.com/username'),
			array('http://twitter.com/username', 'http://twitter.com/username'),
			array('https://twitter.com/username', 'https://twitter.com/username'),
			array('http://twitter.com/#!/username', 'http://twitter.com/#!/username'),
			array('', ''),
		);
	}
	
	/**
	 * 
	 * @dataProvider  provider_twitter
	 */
	public function test_twitter_set($twitter, $expected)
	{
		$twitter_field = new Jam_Field_Twitter;
		$user = Jam::build('test_user');
		
		$this->assertEquals($twitter_field->set($user, $twitter, TRUE), $expected);
	}
}

