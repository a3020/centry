<?php

namespace A3020\Centry\Console\Command;

use A3020\Centry\Entity\Schedule;
use Concrete\Core\Console\Command;
use Concrete\Core\Console\ConsoleAwareInterface;
use Concrete\Core\Job\Job;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSchedulesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('centry:run-schedules')
            ->setDescription('Run automated job schedules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = Application::getFacadeApplication();
        $em = $app->make(EntityManager::class);

        $schedules = $em->getRepository(Schedule::class)
            ->findAll();

        foreach ($schedules as $schedule) {
            $this->runSchedule($schedule, $input, $output);
        }
    }

    private function runSchedule(Schedule $schedule, InputInterface $input, OutputInterface $output)
    {
        if ($schedule->isDue() === false) {
            return;
        }

        $formatter = $this->getHelper('formatter');
        foreach ($this->getJobs($schedule) as $job) {
            /** @var Job $job */

            // Provide the console objects to objects that are aware of the console
            if ($job instanceof ConsoleAwareInterface) {
                $job->setConsole($this->getApplication(), $output, $input);
            }

            $result = $job->executeJob();
            if ($result->isError()) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln(
                        $formatter->formatSection(
                            $job->getJobHandle(), '<error>' . t('Job Failed') . '</error>'
                        )
                    );
                }
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(
                    $formatter->formatSection($job->getJobHandle(), $result->getResultMessage())
                );
            }
        }
    }

    /**
     * Generator for Job objects.
     *
     * We skip jobs that are 'running'.
     *
     * @param Schedule $schedule
     * @return \Generator
     */
    private function getJobs(Schedule $schedule)
    {
        foreach ($schedule->getJobHandles() as $handle) {
            /** @var Job $job */
            $job = Job::getByHandle($handle);
            if (!$job) {
                continue;
            }

            if ($job->getJobStatus() === 'RUNNING') {
                continue;
            }

            yield $job;
        }
    }
}
