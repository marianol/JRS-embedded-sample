<?php

require_once "c:/zwamp/vdrive/web/rest/client/JasperClient.php";

class JasperAttributeServiceTest extends PHPUnit_Framework_TestCase {

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
		$this->attr = new Jasper\Attribute('Gender', 'Robot');
		$this->attr2 = new Jasper\Attribute('Favorite Beer', 'Anchor Steam');
		$this->attrArr = array($this->attr, $this->attr2);

		$this->jc->putUsers($this->newUser);
	}

	public function tearDown() {
		if ($this->newUser !== null) {
			$this->jc->deleteUser($this->newUser);
		}
		$this->newUser = null;
		$this->jc = null;
	}

	/* Tests below */

	public function testPostAttributes_addsOneAttributeData() {
		$this->jc->postAttributes($this->newUser, $this->attr);
		$tempAttr = $this->jc->getAttributes($this->newUser);
		$tempAttrValue = $tempAttr[0]->getAttrValue();
		$tempAttrName = $tempAttr[0]->getAttrName();

		$this->assertEquals('Robot', $tempAttrValue);
		$this->assertEquals('Gender', $tempAttrName);
	}

	public function testPostAttributes_addsMultipleAttributesCount() {
		$attrCount = count($this->jc->getAttributes($this->newUser));
		$this->jc->postAttributes($this->newUser, $this->attrArr);
		$attr2Value = $this->jc->getAttributes($this->newUser);
		$newCount = count($attr2Value);

		$this->assertEquals($attrCount+2, $newCount);
		$this->assertEquals('Anchor Steam', $attr2Value[1]->getAttrValue());

	}

}

?>