<?php

namespace Concrete\Package\Centry\Controller\Api;

use A3020\Centry\Controller\ApiBase;

class Api extends ApiBase
{
    protected function environment()
    {
        return $this->app->make(\A3020\Centry\Environment\Payload::class);
    }

    protected function packages()
    {
        return $this->app->make(\A3020\Centry\Package\Payload::class);
    }

    protected function block_types()
    {
        return $this->app->make(\A3020\Centry\BlockType\Payload::class);
    }

    protected function domains()
    {
        return $this->app->make(\A3020\Centry\Domain\Payload::class);
    }

    protected function jobs()
    {
        return $this->app->make(\A3020\Centry\Job\Payload::class);
    }

    protected function pages_summary()
    {
        return $this->app->make(\A3020\Centry\Page\Summary\Payload::class);
    }

    protected function users_summary()
    {
        return $this->app->make(\A3020\Centry\User\Summary\Payload::class);
    }

    protected function files_summary()
    {
        return $this->app->make(\A3020\Centry\File\Summary\Payload::class);
    }

    protected function logs()
    {
        return $this->app->make(\A3020\Centry\Log\Payload::class);
    }

    protected function logs_summary()
    {
        return $this->app->make(\A3020\Centry\Log\Summary\Payload::class);
    }
}
