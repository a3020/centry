<?php

namespace A3020\Centry\Provider;

use A3020\Centry\Http\Middleware\CentryApiTokenMiddleware;
use A3020\Centry\Http\Middleware\CentryEnabledMiddleware;
use Concrete\Core\Application\Application;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\ServerInterface;
use Concrete\Core\Routing\RouterInterface;
use Concrete\Core\Support\Facade\Url;
use Concrete\Package\Centry\Controller\Api\Api;

class CentryServiceProvider
{
    /** @var Application */
    private $app;

    /** @var Repository */
    protected $config;

    public function __construct(Application $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function register()
    {
        $this->saveDomain();
        $this->saveIdentifier();

        $this->registerMiddleware();
        $this->registerRoutes();
    }

    /**
     * This allows other devs to extend and add middleware as well.
     */
    private function registerMiddleware()
    {
        $this->app->extend(ServerInterface::class, function(ServerInterface $server) {
            return $server->addMiddleware($this->app->make(CentryEnabledMiddleware::class))
                ->addMiddleware($this->app->make(CentryApiTokenMiddleware::class));
        });
    }

    private function registerRoutes()
    {
        $router = $this->app->make(RouterInterface::class);
        $router->register('/centry/api/v'.CENTRY_INSTANCE_API_VERSION, function() {
            $api = $this->app->make(Api::class);
            return $api->invoke('discover');
        });

        $router->register('/centry/api/v'.CENTRY_INSTANCE_API_VERSION.'/{method}', function($method) {
            $api = $this->app->make(Api::class);
            return $api->invoke($method);
        });
    }

    /**
     * Save domains e.g. www.site1.tld to config file.
     *
     * We can't retrieve this from C5, so we have to keep a log.
     * We omit the scheme to prevent duplicate websites in Centry.
     */
    private function saveDomain()
    {
        $domain = rtrim((string) Url::to(''), DISPATCHER_FILENAME);
        $domain = rtrim($domain, "/");

        $domainsFromConfig = $this->config->get('centry.domains', []);
        $domains = array_merge($domainsFromConfig, [$domain]);
        $domains = array_filter($domains);
        $domains = array_unique($domains);

        if ($domainsFromConfig != $domains) {
            $this->config->save('centry.domains', $domains);
        }
    }

    /**
     * We need an identifier for the C5 installation.
     *
     * By also sending an identifier, we can e.g. link multiple hosts with each other.
     */
    private function saveIdentifier()
    {
        $identifier = $this->config->get('centry.identifier');
        if (!$identifier) {
            $this->config->save('centry.identifier', str_random(32));
        }
    }
}
