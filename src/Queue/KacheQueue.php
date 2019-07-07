<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 16:38
 */

namespace Simplex\Queue;


use Kached\Kache;
use Simplex\Queue\Contracts\JobInterface;

class KacheQueue extends AbstractQueue
{

    /**
     * @var Kache
     */
    private $kache;

    /**
     * KacheQueue constructor.
     * @param Kache $kache
     */
    public function __construct(Kache $kache)
    {
        $this->kache = $kache;
    }

    /**
     * Push a job onto the queue
     *
     * @param JobInterface|string $job
     * @param string $queue
     */
    public function push($job, string $queue = 'default')
    {
        $job = json_encode($this->createPayload($job, $queue));

        $response = $this->kache->getConnection()->sendCommand('qadd', $job);
        $this->kache->format($response);
    }

    /**
     * Push a job onto the queue and makes it available after "$delay" milliseconds
     *
     * @param int $delay
     * @param JobInterface|string $job
     * @param string $queue
     */
    public function later(int $delay, $job, string $queue = 'default')
    {
        $job = json_encode(
            array_merge(
                $this->createPayload($job, $queue),
                ['delay' => $delay * 1000]
            )
        );

        $response = $this->kache->getConnection()->sendCommand('qadd', $job);
        $this->kache->format($response);
    }

    /**
     * Gets the next available job from the queue
     *
     * @param string $queue
     * @return JobInterface
     */
    public function pop(string $queue = 'default'): ?JobInterface
    {
        $response = $this->kache->getConnection()->sendCommand('qpop', $queue);
        $payload = $this->kache->format($response);

        if ($payload) {
            /** @var JobInterface $job */
            $job = unserialize($payload['job']);
            $job->setId($payload['id']);

            return $job;
        }

        return null;
    }

    /**
     * Deletes a job from given queue
     *
     * @param int $id
     * @param string $queue
     */
    public function delete(int $id, string $queue)
    {
        $response = $this->kache->getConnection()->sendCommand('qdel', sprintf('%s %d', $queue, $id));
        $this->kache->format($response);
    }


}