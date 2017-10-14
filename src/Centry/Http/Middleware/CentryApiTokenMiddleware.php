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

class CentryApiTokenMiddleware implements MiddlewareInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function process(Request $request, DelegateInterface $frame)
    {
        $requestUri = strtolower($request->getRequestUri());

        if (strpos($requestUri, 'centry/api') !== false) {
            if (!$this->isAuthorized($request)) {
                return new JsonResponse([
                    'error' => t('Unauthorized Request'),
                ], 401);
            }
        }

        /** @var Response $response */
        $response = $frame->next($request);

        return $response;
    }

    /**
     * Return true is request is authorized.
     *
     * Meaning that the token in the header equals the one in the config.
     *
     * @param Request $request
     * @return bool
     */
    private function isAuthorized(Request $request)
    {
        $token = $request->headers->get('x-centry-api-token');
        if (empty($token)) {
            return false;
        }

        $config = $this->app->make(Repository::class);
        return  $token === (string) $config->get('centry.api_token');
    }
}
