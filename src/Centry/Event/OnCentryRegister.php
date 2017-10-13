<?php

namespace A3020\Centry\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

class OnCentryRegister extends GenericEvent
{
    /**
     * @return \A3020\Centry\Payload\Payload
     */
    public function getPayload()
    {
        return $this->getArgument('payload');
    }
}
