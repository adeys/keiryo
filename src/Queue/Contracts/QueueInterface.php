<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 16:10
 */

namespace Keiryo\Queue\Contracts;

interface QueueInterface
{

    /**
     * Push a job onto the queue
     *
     * @param JobInterface $job
     * @param string $queue
     */
    public function push(JobInterface $job, string $queue);

    /**
     * Push a job onto the queue and makes it available after "$delay" milliseconds
     *
     * @param int $delay
     * @param JobInterface $job
     * @param string $queue
     */
    public function later(int $delay, JobInterface $job, string $queue);

    /**
     * Gets the next available job from the queue
     *
     * @param string $queue
     * @return JobInterface|null
     */
    public function pop(string $queue): ?JobInterface;

    /**
     * Deletes a job from given queue
     *
     * @param int $id
     * @param string $queue
     */
    public function delete(int $id, string $queue);
}
