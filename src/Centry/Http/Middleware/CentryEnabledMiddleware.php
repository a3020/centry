<?php

namespace A3020\Centry\Http\Middleware;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Http\Middleware\DelegateInterface;
use Concrete\Core\Http\Middleware\MiddlewareInterface;
use Illuminate\Config\Repository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CentryEnabledMiddleware implements MiddlewareInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function process(Request $request, DelegateInterface $frame)
    {
        $requestUri = $request->getRequestUri();

        if (strpos($requestUri, 'centry/api') !== false) {
            if ($this->isCentryDisabled()) {
                return new JsonResponse([
                    'error' => t('Centry is disabled'),
                ], 405);
            }
        }

        /** @var Response $response */
        $response = $frame->next($request);

        return $response;
    }

    /**
     * @return bool
     */
    private function isCentryDisabled()
    {
        $config = $this->app->make(Repository::class);
        return (bool) $config->get('centry.enabled') === false;
    }
}
