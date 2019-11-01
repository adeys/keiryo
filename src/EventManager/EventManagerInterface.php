<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 14:08
 */

namespace Keiryo\EventManager;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface EventManagerInterface extends EventDispatcherInterface, ListenerProviderInterface
{

    /**
     * Subscribe to an event.
     *
     * @param string $event
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function on(string $event, callable $listener, $priority = 0);

    /**
     * Removes a specific listener from an event.
     *
     * If the listener could not be found, this method will return false. If it
     * was removed it will return true.
     *
     * @param string $event
     * @param callable $listener
     * @return bool
     */
    public function removeListener(string $event, callable $listener);

    /**
     * Removes all listeners.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed. If it is not specified, every listener for every event is
     * removed.
     *
     * @param string|null $event
     * @return void
     */
    public function removeAllListeners(?string $event = null);
}
