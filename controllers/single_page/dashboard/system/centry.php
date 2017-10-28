<?php

namespace Concrete\Package\Centry\Controller\SinglePage\Dashboard\System;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Job\Job;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Url\Resolver\CanonicalUrlResolver;
use Concrete\Core\Url\Resolver\PathUrlResolver;

final class Centry extends DashboardPageController
{
    public function view()
    {
        $config = $this->app->make(Repository::class);

        $this->set('linkToCentryPortal', $this->getLinkToCentryPortal());

        $this->set('endpoint', $this->getEndpoint());
        $this->set('shouldShowSubscribeButton', $this->getShouldShowSubscribeButton());
        $this->set('showJobScheduleSection', $this->getShouldShowJobScheduleSection());
        $this->set('job', $this->getJob());
        $this->set('config', $config);
        $this->set('apiMethods', $this->getApiMethods());
    }

    public function save()
    {
        if (!$this->token->validate('a3020.centry.settings')) {
            $this->error->add($this->token->getErrorMessage());
            return $this->view();
        }

        $config = $this->app->make(Repository::class);
        $config->save('centry.enabled', (bool) $this->post('enabled'));
        $config->save('centry.endpoint', (string) $this->post('endpoint'));
        $config->save('centry.registration_token', (string) $this->post('registration_token'));
        $config->save('centry.api.regenerate_token', (bool) $this->post('regenerate_token'));
        $config->save('centry.domains', $this->getDomains());

        foreach ($this->getApiMethods() as $handle => $name) {
            $configPath = 'centry.api.methods.'.$handle;
            $config->save($configPath, (bool) $this->post('api_access_'.$handle, false));
        }

        $this->flash('success', t('Your settings have been saved.'));

        return $this->redirect('/dashboard/system/centry');
    }
    
    public function subscribed()
    {
        $numberOfDomains = count($this->getDomains());
        $this->flash('success', t2('The domain is successfully subscribed to Centry!',
            'The domains are successfully subscribed to Centry!',
            $numberOfDomains,
            $numberOfDomains
        ));

        return $this->redirect('/dashboard/system/centry');
    }
    
    public function schedule()
    {
        /** @var Job $job */
        $job = Job::getByHandle('centry');
        if (!$job) {
            $this->flash('error', t('The Centry job is not installed!'));
            return $this->redirect('/dashboard/system/centry');
        }

        $job->setSchedule(true, 'days', 1);

        $this->flash('success', t('The Centry job is now scheduled to run daily.'));

        return $this->redirect('/dashboard/system/centry');
    }

    /**
     * @return bool
     */
    private function isConfiguredProperly()
    {
        $config = $this->app->make(Repository::class);
        if (!$config->get('centry.enabled')) {
            return false;
        }

        if (empty($config->get('centry.registration_token'))) {
            return false;
        }

        $job = $this->getJob();
        if (!$job) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function getShouldShowJobScheduleSection()
    {
        if (!$this->isConfiguredProperly()) {
            return false;
        }

        $job = $this->getJob();
        if ($job->isScheduled) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function getShouldShowSubscribeButton()
    {
        return $this->isConfiguredProperly();
    }

    /**
     * @return Job|false
     */
    private function getJob()
    {
        return Job::getByHandle('centry');
    }

    /**
     * @return array
     */
    private function getDomains()
    {
        $domains = explode("\n", str_replace("\r", '', $this->post('domains')));
        return array_map('trim', $domains);
    }

    /**
     * @return array
     */
    private function getApiMethods()
    {
        return [
            'block_types' => t('Block Types'),
            'packages' => t('Packages'),
            'jobs' => t('Automated Jobs'),
            'logs' => t('Logs'),
            'environment' => t('Environment summary'),
            'pages_summary' => t('Pages summary'),
            'files_summary' => t('Files summary'),
            'users_summary' => t('Users summary'),
            'logs_summary' => t('Logs summary'),
        ];
    }

    /**
     * E.g. returns https://centry.nl
     *
     * @return string
     */
    private function getLinkToCentryPortal()
    {
        $endpoint = $this->getEndpoint();

        /** @var PathUrlResolver $urlResolver */
        $urlResolver = $this->app->make(PathUrlResolver::class);
        $url = $urlResolver->resolve([$endpoint]);

        return $url->getBaseUrl();
    }

    /**
     * @return string
     */
    private function getEndpoint()
    {
        $config = $this->app->make(Repository::class);
        return $config->get('centry.endpoint') ?: CENTRY_PORTAL_DEFAULT_ENDPOINT;
    }
}
