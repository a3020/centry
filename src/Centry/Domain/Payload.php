<?php

namespace A3020\Centry\Domain;

use A3020\Centry\Payload\PayloadAbstract;
use Illuminate\Config\Repository;

final class Payload extends PayloadAbstract
{
    /** @var Repository */
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function jsonSerialize()
    {
        return $this->getDomains();
    }

    private function getDomains()
    {
        return $this->config->get('centry.domains', []);
    }
}
