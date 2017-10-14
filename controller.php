<?php

namespace Concrete\Package\Centry;

use A3020\Centry\Http\Middleware\CentryApiTokenMiddleware;
use A3020\Centry\Http\Middleware\CentryEnabledMiddleware;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\ServerInterface;
use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Concrete\Core\Support\Facade\Package as PackageFacade;
use Concrete\Core\Support\Facade\Route;
use Concrete\Core\Support\Facade\Url;

final class Controller extends Package
{
    protected $pkgHandle = 'centry';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.0.9';
    protected $pkgAutoloaderRegistries = [
        'src/Centry' => '\A3020\Centry',
    ];

    /** @var Repository */
    protected $config;

    // Needs to be a class constant, as on_start is not loaded during install.
    const CENTRY_PORTAL_DEFAULT_ENDPOINT = 'https://centry.nl/api/v1';

    public function on_start()
    {
        $this->config = $this->app->make(Repository::class);

        $this->saveDomain();
        $this->saveIdentifier();

        // The version of the API on this C5 installation.
        define('CENTRY_INSTANCE_API_VERSION', 1);

        // Default endpoint of where the data is communicated to.
        // Can be overridden via the settings page.
        define('CENTRY_PORTAL_DEFAULT_ENDPOINT', self::CENTRY_PORTAL_DEFAULT_ENDPOINT);

        // This allows other devs to extend and add middleware as well.
        $this->app->extend(ServerInterface::class, function(ServerInterface $server) {
            return $server->addMiddleware($this->app->make(CentryEnabledMiddleware::class))
                ->addMiddleware($this->app->make(CentryApiTokenMiddleware::class));
        });

        Route::register('/centry/api/v'.CENTRY_INSTANCE_API_VERSION, function() {
            $api = $this->app->make(\Concrete\Package\Centry\Controller\Api\Api::class);
            return $api->invoke('discover');
        });

        Route::register('/centry/api/v'.CENTRY_INSTANCE_API_VERSION.'/{method}', function($method) {
            $api = $this->app->make(\Concrete\Package\Centry\Controller\Api\Api::class);
            return $api->invoke($method);
        });
    }

    public function getPackageName()
    {
        return t('Centry');
    }

    public function getPackageDescription()
    {
        return t('Allows communication to a remote Centry endpoint.');
    }

    public function install()
    {
        $this->config = $this->app->make(Repository::class);
        $pkg = parent::install();
        $this->installEverything($pkg);

        $this->config->save('centry.enabled', true);
        $this->config->save('centry.endpoint', self::CENTRY_PORTAL_DEFAULT_ENDPOINT);
    }

    public function upgrade()
    {
        $this->config = $this->app->make(Repository::class);
        $pkg = PackageFacade::getByHandle($this->pkgHandle);
        $this->installEverything($pkg);
    }

    public function installEverything($pkg)
    {
        $this->installJob($pkg);
        $this->installDashboardPage($pkg);
    }

    private function installJob($pkg)
    {
        /** @var Job $job */
        $job = Job::getByHandle('centry');
        if (!$job) {
            $job = Job::installByPackage('centry', $pkg);
        }

        // Companies running hundreds of websites can use CLI
        // to configure the add-on, e.g. with:
        // c5:config set centry.schedule_job_on_install true
        if ($this->config->get('centry.schedule_job_on_install')) {
            $job->setSchedule(true, 'days', 1);
        }
    }

    private function installDashboardPage($pkg)
    {
        $path = '/dashboard/system/centry';

        /** @var Page $page */
        $page = Page::getByPath($path);
        if ($page && !$page->isError()) {
            return;
        }

        $singlePage = Single::add($path, $pkg);
        $singlePage->update($this->getPackageName());
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

        $config = $this->app->make(Repository::class);

        $domainsFromConfig = $config->get('centry.domains', []);
        $domains = array_merge($domainsFromConfig, [$domain]);
        $domains = array_filter($domains);
        $domains = array_unique($domains);

        if ($domainsFromConfig != $domains) {
            $config->save('centry.domains', $domains);
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
