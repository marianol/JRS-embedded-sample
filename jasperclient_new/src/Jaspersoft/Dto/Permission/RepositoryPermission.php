<?php
namespace Jaspersoft\Dto\Permission;

class RepositoryPermission implements \JsonSerializable {

	public $uri;
	public $recipient;
	public $mask;

	public function __construct($uri, $recipient, $mask) {
		$this->uri = $uri;
		$this->recipient = $recipient;
		$this->mask = $mask;
	}
	
	public function jsonSerialize() {
		$data = array();
        foreach (get_object_vars($this) as $k => $v) {
            if (isset($v)) {
                $data[$k] = $v;
            }
        }
        return $data;
	}

}

?>