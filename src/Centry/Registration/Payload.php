<?php

namespace A3020\Centry\Registration;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;

final class Payload extends PayloadAbstract
{
    use ApplicationAwareTrait;

    /** @var Repository */
    private $config;
    private $jobUrl;
    private $apiToken;
    private $apiEndpoint;

    public function __construct(Repository $config, $jobUrl, $apiToken, $apiEndpoint)
    {
        $this->config = $config;
        $this->jobUrl = (string) $jobUrl;
        $this->apiToken = (string) $apiToken;
        $this->apiEndpoint = (string) $apiEndpoint;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'job_url' => $this->jobUrl,
            'api_endpoint' => $this->apiEndpoint,
            'api_token' => $this->apiToken,
            'domains' => $this->getDomains(),
        ];
    }

    /**
     * A list of domains. Each domain will be a record in Centry.
     *
     * @return PayloadAbstract
     */
    private function getDomains()
    {
        return $this->app->make(\A3020\Centry\Domain\Payload::class);
    }

    /**
     * The identifier for this C5 installation.
     *
     * @return string
     */
    private function getIdentifier()
    {
        return (string) $this->config->get('centry.identifier');
    }
}
