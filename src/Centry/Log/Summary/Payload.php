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
            'total_logs' => $this->getTotalLogs(),
            'total_exceptions' => $this->getTotalExceptions(),
        ];
    }

    private function getTotalLogs()
    {
        return (int) $this->db->fetchColumn("
            SELECT COUNT(1)
            FROM Logs
        ");
    }

    private function getTotalExceptions()
    {
        return (int) $this->db->fetchColumn("
            SELECT COUNT(1)
            FROM Logs
            WHERE channel = 'exceptions'
        ");
    }
}
