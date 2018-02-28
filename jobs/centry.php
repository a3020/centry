<?php

namespace Concrete\Package\Centry\Job;

use A3020\Centry\Exception\CouldNotSubscribeException;
use A3020\Centry\Registration\Payload as RegistrationPayload;
use Concrete\Core\Job\Job;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Exception;
use Illuminate\Config\Repository;

final class Centry extends Job
{
    /** @var \Concrete\Core\Application\Application */
    private $appInstance;

    /** @var Repository */
    protected $config;

    public function __construct()
    {
        $this->appInstance = Application::getFacadeApplication();
        $this->config = $this->appInstance->make(Repository::class);
    }

    public function getJobName()
    {
        return t('Centry');
    }

    public function getJobDescription()
    {
        return t('Periodically sends website data to a remote Centry endpoint.');
    }

    public function run()
    {
        $this->preRunCheck();

        $payload = $this->appInstance->make(RegistrationPayload::class, [
            'config' => $this->config,
            'jobUrl' => $this->getJobUrl(),
            'apiEndpoint' => $this->getApiEndpoint(),
            'apiToken' => $this->getAndUpdateApiToken(),
        ]);

        try {
            $this->register($payload);
        } catch (CouldNotSubscribeException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            if ($e->getPrevious()) {
                $e = $e->getPrevious();
            }

            $error = $e->getMessage() .' '.$e->getTraceAsString();

            /** @var \Concrete\Core\Logging\Logger $log */
            $log = $this->appInstance->make('log');
            $log->addError($error);

            throw new Exception(t('An error occurred. Go to Logs.'));
        }

        return t('All domains are successfully subscribed to Centry.');
    }

    /**
     * @return \League\URL\URLInterface
     */
    public function getJobUrl()
    {
        return Url::to(
            '/ccm/system/jobs/run_single?auth=' . $this->generateAuth() . '&jID=' . $this->getJobID()
        );
    }

    private function preRunCheck()
    {
        if (!$this->config->get('centry.enabled')) {
            throw new Exception(t('Centry is not enabled.'));
        }

        if (!$this->getRegistrationToken()) {
            throw new Exception(t('No registration token defined.'));
        }

        if ($this->isLocalHost()) {
            throw new Exceptiont(t("Centry Portal won't be able to connect to your local environment."));
        }
    }

    /**
     * @param RegistrationPayload $payload
     * @throws Exception
     */
    protected function register($payload)
    {
        $headers = array(
            'Content-Type: application/json',
            'X-CENTRY-API-TOKEN: ' . $this->getRegistrationToken(),
        );

        $ch = curl_init($this->getRegistrationEndpoint());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 200) {
            return;
        }

        if ($statusCode === 0) {
            throw new CouldNotSubscribeException(t('Host not found: %s', $this->getRegistrationEndpoint()));
        }

        $data = json_decode($response, true);
        if ($data && isset($data['message'])) {
            throw new CouldNotSubscribeException($data['message']);
        }

        throw new Exception(
            t('Status code: %s. Response: %s.',
                $statusCode,
                $response
            )
        );
    }

    /**
     * E.g. https://centry.nl/api/v1/register
     *
     * @return string
     */
    private function getRegistrationEndpoint()
    {
        return $this->getEndpoint() . '/register';
    }

    /**
     * E.g. https://centry.nl/api/v1
     *
     * @return string
     */
    private function getEndpoint()
    {
        $endpoint = $this->config->get('centry.endpoint', CENTRY_PORTAL_DEFAULT_ENDPOINT);
        return rtrim($endpoint, "/");
    }

    /**
     * Token needed to subscribe domains in Centry.
     *
     * @return string
     */
    private function getRegistrationToken()
    {
        return (string) $this->config->get('centry.registration_token');
    }

    /**
     * @return \League\URL\URLInterface
     */
    private function getApiEndpoint()
    {
        return Url::to('/centry/api/v' . CENTRY_INSTANCE_API_VERSION);
    }

    /**
     * Regenerate and retrieve API token.
     *
     * @return string
     */
    private function getAndUpdateApiToken()
    {
        $token = $this->generateApiToken();
        $this->config->save('centry.api.token', $token);

        return $token;
    }

    /**
     * Generates an API token.
     *
     * The API token will be sent to Centry Portal so
     * it can then send requests to this C5 instance.
     *
     * Each time Centry Add-on 'subscribes' / 'registers'
     * a new token will be generated.
     *
     * @return string
     */
    private function generateApiToken()
    {
        return str_random(64);
    }

    /**
     * @return bool
     */
    private function isLocalHost()
    {
        $localAddresses = ['127.0.0.1', '::1'];

        return in_array($_SERVER['REMOTE_ADDR'], $localAddresses);
    }
}
