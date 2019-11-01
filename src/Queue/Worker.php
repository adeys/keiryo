<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 21:47
 */

namespace Keiryo\Queue;

use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Queue\Contracts\JobInterface;
use Keiryo\Queue\Contracts\QueueInterface;
use Keiryo\Queue\Event\JobFailedEvent;
use Keiryo\Queue\Event\JobStartingEvent;
use Keiryo\Queue\Event\JobSuccessEvent;

class Worker
{

    /**
     * @var QueueManager
     */
    private $manager;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var int
     */
    private $sleep = 5;

    /**
     * Worker constructor.
     * @param QueueManager $manager
     * @param EventManagerInterface $eventManager
     */
    public function __construct(QueueManager $manager, EventManagerInterface $eventManager)
    {
        $this->manager = $manager;
        $this->eventManager = $eventManager;
    }

    /**
     * Listens to a queue for new jobs to run
     *
     * @param string $queue
     * @param string|null $connection
     */
    public function listen(string $queue = 'default', ?string $connection = null)
    {
        $connection = $this->manager->connection($connection);
        while (true) {
            $job = $connection->pop($queue);
            if (null !== $job) {
                $this->runJob($job, $connection);
            } else {
                $this->sleep($this->sleep);
            }
        }
    }

    /**
     * @param JobInterface $job
     * @param QueueInterface $queue
     */
    protected function runJob(JobInterface $job, QueueInterface $queue)
    {
        try {
            $this->eventManager->dispatch(new JobStartingEvent($job));
            $job->fire();
            $this->eventManager->dispatch(new JobSuccessEvent($job));
        } catch (\Exception $exception) {
            $this->eventManager->dispatch(new JobFailedEvent($job));
            //echo $exception->getMessage() . "\n";
        }
    }

    /**
     * @param int $seconds
     */
    protected function sleep(int $seconds)
    {
        sleep($seconds);
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager(): EventManagerInterface
    {
        return $this->eventManager;
    }

    /**
     * @param int $sleep
     */
    public function setSleep(int $sleep): void
    {
        $this->sleep = $sleep;
    }
}
