<?php
namespace Jasper;

class Role {

	public $roleName;
	public $tenantId;
	public $externallyDefined;


	public function __construct(
		$roleName = null,
		$tenantId = null,
		$externallyDefined = 'false')
	{
		$this->roleName = ($roleName !== null) ? (string) $roleName : null;
		$this->tenantId = ($tenantId !== null) ? (string) $tenantId : null;
		$this->externallyDefined = ($externallyDefined !== null) ? (string) $externallyDefined : 'false';

	}

	public function getRoleName() { return $this->roleName; }
	public function getTenantId() { return $this->tenantId; }
	public function getExternallyDefined() { return $this->externallyDefined; }

	public function setRoleName($roleName) { $this->roleName = $roleName; }
	public function setTenantId($tenantId) { $this->tenantId = $tenantId; }
	public function setExternallyDefined($externallyDefined) { $this->externallyDefined = $externallyDefined; }


	public function asXML() {
		$seri_opt = array(
			'indent' => '     ',
			'rootName' => 'role',
			'ignoreNull' => true
			);
		$seri = new \XML_Serializer($seri_opt);
		$res = $seri->serialize($this);
		if ($res === true) {
			return $seri->getSerializedData();
		} else {
			return false;
		}
	}

	public function __toString() {
		return htmlentities($this->asXML());
	}
}

?>