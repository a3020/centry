<?php

namespace A3020\Centry\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="CentrySchedules",
 * )
 */
class Schedule
{
    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $cronExpression = '';

    /**
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $jobHandles = [];

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getCronExpression()
    {
        return (string) $this->cronExpression;
    }

    /**
     * @return array
     */
    public function getJobHandles()
    {
        return (array) $this->jobHandles;
    }

    /**
     * @param string $cronExpression
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = (string) $cronExpression;
    }

    /**
     * @param array $jobHandles
     */
    public function setJobHandles($jobHandles)
    {
        $this->jobHandles = (array) $jobHandles;
    }
}
