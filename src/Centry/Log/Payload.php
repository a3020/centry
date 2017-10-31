<?php

namespace A3020\Centry\Log;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Database\Connection\Connection;

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
        return $this->getLogs();
    }

    private function getLogs()
    {
        $logs = $this->db->fetchAll('
            SELECT * FROM Logs
            ORDER BY logID DESC
            LIMIT ' . $this->getMaxNumberOfLogs()
        );

        return array_map(function($log) {
            return [
                'log_id' => (int) $log['logID'],
                'log_channel' => $log['channel'],
                'log_time' => (int) $log['time'],
                'log_message' => $log['message'],
                'log_level' => (int) $log['level'],
                'log_user_id' => $log['uID'] ? (int) $log['uID'] : null,
            ];
        }, $logs);
    }

    private function getMaxNumberOfLogs()
    {
        return (int) $this->config->get('centry.api.logs.max', 300);
    }
}
