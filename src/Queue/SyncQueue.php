<?php
/**
 * Created by PhpStorm.
 * User: K. Raph
 * Date: 05/11/2019
 * Time: 20:33
 */

namespace Keiryo\Queue;


use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Queue\Contracts\JobInterface;
use Keiryo\Queue\Contracts\QueueInterface;
use Keiryo\Queue\Event\JobFailedEvent;
use Keiryo\Queue\Event\JobStartingEvent;
use Keiryo\Queue\Event\JobSuccessEvent;

class SyncQueue implements QueueInterface
{

    /**
     * @var EventManagerInterface
     */
    private $manager;

    /**
     * SyncQueue constructor.
     * @param EventManagerInterface $manager
     */
    public function __construct(EventManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Push a job onto the queue and makes it available after "$delay" milliseconds
     *
     * @param int $delay
     * @param JobInterface $job
     * @param string $queue
     */
    public function later(int $delay, JobInterface $job, string $queue)
    {
        $this->push($job, $queue);
    }

    /**
     * Push a job onto the queue
     *
     * @param JobInterface $job
     * @param string $queue
     */
    public function push(JobInterface $job, string $queue)
    {
        try {
            $this->manager->dispatch(new JobStartingEvent($job));
            $job->fire();
            $this->manager->dispatch(new JobSuccessEvent($job));
        } catch (\Exception $exception) {
            $this->manager->dispatch(new JobFailedEvent($job));
        }
        $job->fire();
    }

    /**
     * Gets the next available job from the queue
     *
     * @param string $queue
     * @return JobInterface|null
     */
    public function pop(string $queue): ?JobInterface
    {
        return null;
    }

    /**
     * Deletes a job from given queue
     *
     * @param int $id
     * @param string $queue
     * @return null
     */
    public function delete(int $id, string $queue)
    {
        return null;
    }
}