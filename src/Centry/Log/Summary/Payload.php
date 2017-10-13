<?php

namespace A3020\Centry\Log\Summary;

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
        return [
            'total_exceptions' => $this->getTotalExceptions(),
            'last_exceptions' => $this->getLastExceptions(15),
        ];
    }

    private function getTotalExceptions()
    {
        return (int) $this->db->fetchColumn("
            SELECT COUNT(1) 
            FROM Logs
            WHERE channel = 'exceptions'
        ");
    }

    /**
     * @param $limit
     * @return int
     */
    private function getLastExceptions($limit)
    {
        return (array) $this->db->fetchAll("
            SELECT message, time, uID
            FROM Logs
            WHERE channel = 'exceptions'
            ORDER BY logID DESC
            LIMIT 0, ".$limit
        );
    }
}
