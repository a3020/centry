<?php

namespace A3020\Centry\Log;

use A3020\Centry\Event\OnCentryGetLogs;
use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Database\Connection\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class Payload extends PayloadAbstract
{
    const MAX_NUMBER_OF_LOGS = 300;

    /** @var Connection */
    private $db;

    /** @var EventDispatcherInterface */
    private $director;

    public function __construct(Connection $db, EventDispatcherInterface $director)
    {
        $this->db = $db;
        $this->director = $director;
    }

    public function jsonSerialize()
    {
        return $this->getLogs();
    }

    private function getLogs()
    {
        /** @var OnCentryGetLogs $event */
        $event = new OnCentryGetLogs();
        $event = $this->director->dispatch('on_centry_get_logs', new $event);

        if ($event->getLogs()) {
            return $event->getLogs();
        }

        $logs = $this->db->fetchAll('
            SELECT * FROM Logs
            LIMIT ' . static::MAX_NUMBER_OF_LOGS
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
}
