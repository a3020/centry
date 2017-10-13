<?php

namespace A3020\Centry\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

class OnCentryGetLogs extends GenericEvent
{
    /** @var array */
    protected $logs;

    /**
     * @return array|null
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param array $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }
}
