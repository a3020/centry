<?php

namespace A3020\Centry\Exception;

use Exception;
use Throwable;

class CouldNotSubscribeException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = t('Could not subscribe to Centry. ') . $message;
        parent::__construct($message);
    }
}
