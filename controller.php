<?php

namespace Concrete\Package\Centry;

use A3020\Centry\Provider\CentryServiceProvider;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Concrete\Core\Support\Facade\Package as PackageFacade;

final class Controller extends Package
{
    protected $pkgHandle = 'centry';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.2.8';
    protected $pkgAutoloaderRegistries = [
        'src/Centry' => '\A3020\Centry',
    ];

    /** @var Repository */
    protected $config;

    // Needs to be a class constant, as on_start is not loaded during install.
    const CENTRY_PORTAL_DEFAULT_ENDPOINT = 'https://centry.nl/api/v1';

    public function getPackageName()
    {
        return t('Centry');
    }

    public function getPackageDescription()
    {
        return t('Allows communication to a remote Centry endpoint.');
    }

    public function on_start()
    {
        $this->loadDependencies();

        // The version of the API on this C5 installation.
        define('CENTRY_INSTANCE_API_VERSION', 1);

        // Default endpoint of where the data is communicated to.
        // Can be overridden via the settings page.
        define('CENTRY_PORTAL_DEFAULT_ENDPOINT', self::CENTRY_PORTAL_DEFAULT_ENDPOINT);

        $provider = $this->app->make(CentryServiceProvider::class);
        $provider->register();
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

        $job->setSchedule(true, 'days', 1);
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

    public function uninstall()
    {
        parent::uninstall();

        $db = $this->app->make('database')->connection();
        $db->executeQuery("DROP TABLE IF EXISTS CentrySchedules");
    }

    /**
     * Load Composer files.
     *
     * If the C5 installation is Composer based, the vendor directory will
     * be in the root directory. Therefore we first check if it's present.
     */
    private function loadDependencies()
    {
        $autoloadFile = $this->getPackagePath() . '/vendor/autoload.php';
        if (file_exists($autoloadFile)) {
            require_once $autoloadFile;
        }
    }
}
