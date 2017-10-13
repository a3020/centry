<?php

namespace A3020\Centry\Package;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;

final class Payload extends PayloadAbstract
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function jsonSerialize()
    {
        return $this->getPackages();
    }

    private function getPackages()
    {
        $packages = $this->db->fetchAll('
            SELECT * FROM Packages ORDER BY pkgID
        ');

        return array_map(function($package) {
            return array(
                'pkg_id' => $package['pkgID'],
                'pkg_name' => $package['pkgName'],
                'pkg_handle' => $package['pkgHandle'],
                'pkg_description' => $package['pkgDescription'],
                'pkg_installed_at' => $package['pkgDateInstalled'],
                'pkg_is_installed' => $package['pkgIsInstalled'],
                'pkg_version_installed' => $package['pkgVersion'],
                'pkg_version_available' => $package['pkgAvailableVersion'],
            );
        }, $packages);
    }
}
