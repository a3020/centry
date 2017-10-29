<?php

namespace A3020\Centry\Environment;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Illuminate\Config\Repository;

final class Payload extends PayloadAbstract
{
    /** @var Connection */
    private $db;

    /** @var Repository */
    private $config;

    public function __construct(Connection $db, Repository $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function jsonSerialize()
    {
        return [
            'c5_version' => $this->getC5Version(),
            'php_version' => $this->getPhpVersion(),
            'ip_address' => $this->getIpAddress(),
            'base_directory_path' => $this->getBaseDirectoryPath(),
            'overrides' => $this->getOverrides(),
        ];
    }

    private function getC5Version()
    {
        return (string) $this->config->get('concrete.version_installed');
    }

    private function getPhpVersion()
    {
        return PHP_VERSION;
    }

    private function getIpAddress()
    {
        return $_SERVER['SERVER_ADDR'];
    }

    private function getOverrides()
    {
        /** @var \Concrete\Core\System\Info $info */
        $info = $this->app->make(\Concrete\Core\System\Info::class);

        // This method doesn't exist in v8.0.0
        if (method_exists($info, 'getOverrideList')) {
            return $info->getOverrideList();
        }

        return explode(', ', $info->getOverrides());
    }

    /**
     * Return absolute root path of current installation.
     *
     * Available from version 2.1.3.
     *
     * @return string
     */
    private function getBaseDirectoryPath()
    {
        return DIR_BASE;
    }
}
