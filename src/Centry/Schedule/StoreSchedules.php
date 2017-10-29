<?php

namespace A3020\Centry\Schedule;

use A3020\Centry\Entity\Schedule;
use A3020\Centry\Exception\InvalidScheduleException;
use Concrete\Core\Http\Request;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class StoreSchedules
{
    /** @var Request */
    private $request;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(Request $request, EntityManagerInterface $entityManager)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
    }

    public function handle()
    {
        try {
            $content = $this->request->getContent();
            $schedules = json_decode($content, true);

            $this->validate($schedules);
            $this->deleteOldSchedules();
            $this->storeSchedules($schedules);
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        return [
            'error' => false,
        ];
    }

    private function deleteOldSchedules()
    {
        $entities = $this->entityManager->getRepository(Schedule::class)
            ->findAll();
        array_walk($entities, function($entity) {
            $this->entityManager->remove($entity);
        });

        $this->entityManager->flush();
    }

    /**
     * @param array $schedules
     */
    private function storeSchedules($schedules)
    {
        foreach ($schedules as $schedule) {
            $entity = new Schedule();
            $entity->setCronExpression($schedule['cron_expression']);
            $entity->setJobHandles($schedule['job_handles']);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $schedules
     * @throws InvalidScheduleException
     */
    private function validate($schedules)
    {
        if (!is_array($schedules)) {
            throw InvalidScheduleException::invalidPayload();
        }

        foreach ($schedules as $schedule) {
            if (!isset($schedule['cron_expression'])) {
                throw InvalidScheduleException::invalidCronExpression();
            }

            if (!CronExpression::isValidExpression($schedule['cron_expression'])) {
                throw InvalidScheduleException::invalidCronExpression();
            }

            if (!isset($schedule['job_handles']) || !is_array($schedule['job_handles'])) {
                throw InvalidScheduleException::invalidJobHandle();
            }

            // Do not allow multidimensional a job handles array
            foreach ($schedule['job_handles'] as $jobHandle) {
                if (is_array($jobHandle)) {
                    throw InvalidScheduleException::invalidJobHandle();
                }
            }
        }
    }
}
