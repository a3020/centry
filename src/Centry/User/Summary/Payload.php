<?php

namespace A3020\Centry\User\Summary;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\User\UserList;

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
        return [
            'total_users' => $this->getTotalUsers(),
            'last_login' => $this->getLastLoginDate(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalUsers()
    {
        $ul = new UserList();
        if (method_exists($ul, 'ignorePermissions')) {
            // This method doesn't exist in v8.0
            $ul->ignorePermissions();
        }
        return (int) $ul->getTotalResults();
    }

    /**
     * @return int timestamp
     */
    private function getLastLoginDate()
    {
        return $this->db->fetchColumn('
            SELECT uLastLogin FROM Users 
            ORDER BY uLastLogin DESC LIMIT 0,1
        ');
    }
}
