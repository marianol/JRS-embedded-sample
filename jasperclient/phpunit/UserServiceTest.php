<?php

require_once "c:/zwamp/vdrive/web/rest/client/JasperClient.php";

class JasperUserServiceTest extends PHPUnit_Framework_TestCase {

	protected $jc;
	protected $newUser;

	public function setUp() {
		$this->jc = new Jasper\JasperClient('localhost', 8080, 'jasperadmin', 'jasperadmin', '/jasperserver-pro', 'organization_1');
		$this->newUser = new Jasper\User(
			'TEST_USER',
			'TEST_PASS',
			'test@example.com',
			'Dr. Test User',
			'organization_1',
			array('externallyDefined' => 'false', 'roleName' => 'ROLE_USER'),
			'true'
			);
	}

	public function tearDown() {
		if ($this->newUser !== null) {
			$this->jc->deleteUser($this->newUser);
		}
		$this->newUser = null;
		$this->jc = null;
	}

	/* Tests below */

	public function testGetUser_getsCorrectUser() {
		$this->jc->putUsers($this->newUser);
		$tempUser = $this->jc->getUsers($this->newUser->getUsername());
		$this->assertEquals($this->newUser->getFullName(), $tempUser->getFullName());
	}

	public function testCreateUser_increasesUserCountByOne() {
		$userCount = count($this->jc->getUsers());
		$this->jc->putUsers($this->newUser);
		$this->assertEquals($userCount+1, (count($this->jc->getUsers())));
	}

	/**
	 * @depends testCreateUser_increasesUserCountByOne
	 */
	public function testDeleteUser_reducesUserCountByOne() {
		$userCount = count($this->jc->getUsers());
		$this->jc->putUsers($this->newUser);
		$this->jc->deleteUser($this->newUser);
		$this->newUser = null;
		$this->assertEquals($userCount, count($this->jc->getUsers()));
	}

	/**
	 * @depends testCreateUser_increasesUserCountByOne
	 */
	public function testPostUser_changesUserData() {
		$this->jc->putUsers($this->newUser);
		$this->newUser->setEmailAddress('test@dude.com');
		$this->jc->postUser($this->newUser);
		$userStore = $this->jc->getUsers('TEST_USER');
		$this->assertEquals($userStore->getEmailAddress(), 'test@dude.com');
	}

}

?>