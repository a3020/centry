<?php

namespace A3020\Centry\Controller;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiBase extends Controller
{
    public function invoke($method)
    {
        $verb = $this->getHttpVerb();
        $method = $verb !== 'get' ? $method.'__'.$verb : $method;

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

        $response = new JsonResponse($this->{$method}());
        $response->send();
        exit;
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

    /**
     * Returns e.g. 'get', or 'post'.
     *
     * @return string
     */
    private function getHttpVerb()
    {
        if ($this->request->isPost()) {
            $verb = 'post';
        }

        return isset($verb) ? $verb : 'get';
    }
}
