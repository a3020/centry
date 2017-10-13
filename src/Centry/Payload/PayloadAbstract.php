<?php

namespace A3020\Centry\Payload;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use JsonSerializable;

abstract class PayloadAbstract implements JsonSerializable, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [];
    }
}
