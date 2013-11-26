<?php

require_once "c:/zwamp/vdrive/web/rest/client/JasperClient.php";

class JasperOrganizationServiceTest extends PHPUnit_Framework_TestCase {

	protected $jc;
	protected $testOrg;
	protected $subOrg;

	public function setUp() {
		$this->jc = new Jasper\JasperClient('localhost', 8080, 'jasperadmin', 'jasperadmin', '/jasperserver-pro', 'organization_1');
		$this->testOrg = new Jasper\Organization(
			'testorg',
			'testorg',
			'organization_1',
			'testorg'
		);
		$this->subOrg = new Jasper\Organization(
				'suborg',
				'suborg',
				'testorg',
				'suborg'
		);
	}

	public function tearDown() {
		if ($this->testOrg !== null) {
			$this->jc->deleteOrganization($this->testOrg);
		}

		$this->testOrg = null;
		$this->subOrg = null;
		$this->jc = null;
	}

	/* Tests below */

	public function testPutGetOrganization_withoutSubOrganzationFlag() {
		$this->jc->putOrganization($this->testOrg);
		$tempOrg = $this->jc->getOrganization($this->testOrg->getId(), false);
		$this->assertEquals($this->testOrg->getId(), $tempOrg->getId());
	}

	public function testPutGetOrganization_withSubOrganizationFlag() {
		$this->jc->putOrganization($this->testOrg);
		$this->jc->putOrganization($this->subOrg);

		$tempOrg = $this->jc->getOrganization($this->testOrg->getId(), true);
		$this->assertEquals($this->subOrg->getId(), $tempOrg->getId());

	}

	public function testPutPostOrganization_successfullyUpdatesOrganization() {
		$this->jc->putOrganization($this->testOrg);
		$this->testOrg->setTenantDesc('TEST_TEST_TEST');
		$this->jc->postOrganization($this->testOrg);

		$tempOrg = $this->jc->getOrganization($this->testOrg->getId());

		$this->assertEquals('TEST_TEST_TEST', $tempOrg->getTenantDesc());
	}

}

?>