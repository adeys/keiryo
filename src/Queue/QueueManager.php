<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 20:05
 */

namespace Keiryo\Queue;

use Keiryo\Queue\Contracts\QueueInterface;

class QueueManager
{

    /**
     * @var QueueInterface[]
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var string
     */
    protected $default;

    /**
     * Register a new driver
     *
     * @param string $driver
     * @param \Closure $resolver
     */
    public function register(string $driver, \Closure $resolver)
    {
        $this->resolvers[$driver] = $resolver;
    }

    /**
     * Gets a connection
     *
     * @param string|null $name
     * @return QueueInterface
     */
    public function connection(?string $name = null): QueueInterface
    {
        $name = $name ?? $this->default;

        if (!array_key_exists($name, $this->drivers)) {
            $this->drivers[$name] = $this->resolve($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Resolves a connection
     *
     * @param string $name
     * @return QueueInterface
     */
    protected function resolve(string $name): QueueInterface
    {
        $resolver = $this->resolvers[$name] ?? null;

        if ($resolver) {
            return call_user_func($resolver);
        }

        throw new \InvalidArgumentException("Queue driver '$name' is not registered/supported");
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault(string $default): void
    {
        $this->default = $default;
    }
}
