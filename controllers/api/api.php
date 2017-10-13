<?php

namespace Concrete\Package\Centry\Controller\Api;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class Api extends Controller
{
    public function invoke($method)
    {
        if (!$this->isMethodAllowed($method)) {
            return new JsonResponse([
                'error' => t('Method Not Allowed'),
            ], 405);
        }

        if (!$this->isMethodImplemented($method)) {
            return new JsonResponse([
                'error' => t('Method Not Implemented'),
            ], 501);
        }

        return new JsonResponse($this->{$method}());
    }

    /**
     * @todo to be implemented
     */
    protected function discover()
    {
        return [];
    }

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

    /**
     * Return false if method is disabled via the config.
     *
     * @param string $method
     * @return bool
     */
    private function isMethodAllowed($method)
    {
        $config = $this->app->make(Repository::class);

        return (bool) $config->get('centry.api.methods.'.$method, true);
    }

    /**
     * Return false if the method is not implemented.
     *
     * @param string $method
     * @return bool
     */
    private function isMethodImplemented($method)
    {
        return method_exists($this, $method);
    }
}