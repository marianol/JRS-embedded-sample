<?php
namespace Jaspersoft\Service\Criteria;

use Jaspersoft\Tool\Util;

class Criterion {

    public function toArray() {
        return get_object_vars($this);
    }

    public function toQueryParams() {
        return Util::query_suffix($this->toArray());
    }

}