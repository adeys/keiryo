<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 18:16
 */

namespace Keiryo\Queue;

use Keiryo\Helper\Str;
use Keiryo\Queue\Contracts\JobInterface;
use Keiryo\Queue\Contracts\QueueInterface;

abstract class AbstractQueue implements QueueInterface
{

    /**
     * Creates array payload to submit
     *
     * @param JobInterface $job
     * @param string $queue
     * @return array
     * @throws \Exception
     */
    protected function createPayload(JobInterface $job, string $queue): array
    {
        return [
            'id' => $this->getId(),
            'queue' => $queue,
            'job' => [
                'class' => get_class($job),
                'body' => serialize($job)
            ]
        ];
    }

    /**
     * Get random id for job
     *
     * @return string
     * @throws \Exception
     */
    protected function getId(): string
    {
        return Str::random(10);
    }
}
