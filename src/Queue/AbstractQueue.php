<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 18:16
 */

namespace Simplex\Queue;

use Simplex\Helper\Str;
use Simplex\Queue\Contracts\JobInterface;
use Simplex\Queue\Contracts\QueueInterface;

abstract class AbstractQueue implements QueueInterface
{

    /**
     * Creates array payload to submit
     *
     * @param $job
     * @param string $queue
     * @return array
     * @throws \Exception
     */
    protected function createPayload($job, string $queue): array
    {
        return [
            'id' => $this->getId(),
            'queue' => $queue,
            'job' => [
                'class' => get_class($job),
                'body' => $job instanceof JobInterface ? $job = serialize($job) : $job
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
