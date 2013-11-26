<?php

require_once "c:\\zwamp\\vdrive\\web\\rest\\client\\JasperClient.php";

class JasperRoleServiceTest extends PHPUnit_Framework_TestCase {

	protected $jc;
	protected $newUser;
	protected $newRole;

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

		$this->newRole = new Jasper\Role(
				'NOT_A_REAL_ROLE', 'organization_1');
		$this->jc->putUsers($this->newUser);
	}

	public function tearDown() {
		if ($this->newUser !== null) {
			$this->jc->deleteUser($this->newUser);
		}
		if ($this->newRole !== null) {
			$this->jc->deleteRole($this->newRole);
		}
		$this->newUser = null;
		$this->newRole = null;
		$this->jc = null;
	}

	/* Tests below */

	public function testPutRole_addsRole() {
		$this->jc->putRole($this->newRole);
		$newRoleCount = count($this->jc->getRoles($this->newRole->getRoleName(), $this->newRole->getTenantId()));
		$this->assertEquals($newRoleCount, 1);
	}

	/**
	 * @depends testPutRole_addsRole
	 */
	public function testDeleteRole_removesRole() {
		$this->jc->putRole($this->newRole);
		$roleCount = count($this->jc->getRoles($this->newRole->getRoleName(), $this->newRole->getTenantId()));
		$this->jc->deleteRole($this->newRole);
		$this->assertEquals(0, count($this->jc->getRoles($this->newRole->getRoleName(), $this->newRole->getTenantId())));
		$this->newRole = null; 	// must nullify so tearUp doesn't interfere with results
	}

	public function testPostRole_updatesRole() {
		$this->jc->putRole($this->newRole);
		$this->jc->postRole($this->newRole, 'ROLE_TESTER');
		$tempRole = $this->jc->getRoles($this->newRole->getRoleName(), 'organization_1');
		$this->assertEquals($this->newRole->getRoleName(), $tempRole->getRoleName());
	}

}

?>