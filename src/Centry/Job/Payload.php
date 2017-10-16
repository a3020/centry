<?php

namespace A3020\Centry\Job;

use A3020\Centry\Payload\PayloadAbstract;
use Concrete\Core\Job\Job;
use Illuminate\Config\Repository;
use Concrete\Core\Support\Facade\Url;

/**
 * Returns a list of installed jobs.
 */
final class Payload extends PayloadAbstract
{
    /** @var Repository */
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function jsonSerialize()
    {
        return $this->getJobs();
    }

    private function getJobs()
    {
        $jobs = Job::getList();

        return array_map(function($job) {
            /** @var Job $job */

            return [
                'job_id' => $job->getJobID(),
                'job_handle' => $job->getJobHandle(),
                'job_name' => $job->getJobName(),
                'job_description' => $job->getJobDescription(),
                'job_last_status_text' => $job->getJobLastStatusText(),
                'job_is_scheduled' => (int) $job->isScheduled,
                'job_url' => $this->getJobUrl($job),
                'job_last_run' => $job->getJobDateLastRun() ? strtotime($job->getJobDateLastRun()) : null,
                'job_schedule_interval' => $job->isScheduled ? $job->scheduledInterval : null,
                'job_schedule_value' => $job->isScheduled ? $job->scheduledValue : null,
                'job_is_queueable' => $job->supportsQueue(),
            ];
        }, $jobs);
    }

    /**
     * @param Job $job
     * @return string
     */
    public function getJobUrl(Job $job)
    {
        return (string) Url::to(
            '/ccm/system/jobs/run_single?auth=' . $job->generateAuth() . '&jID=' . $job->getJobID()
        );
    }
}
