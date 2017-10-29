<?php

namespace A3020\Centry\Exception;

use Exception;

class InvalidScheduleException extends Exception
{
    public static function invalidPayload()
    {
        return new static(t('Invalid schedule(s)'));
    }

    public static function invalidCronExpression()
    {
        return new static(t('Invalid cron expression(s)'));
    }

    public static function invalidJobHandle()
    {
        return new static(t('Invalid job handle(s)'));
    }
}
