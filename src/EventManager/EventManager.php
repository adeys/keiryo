<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 14:09
 */

namespace Keiryo\EventManager;

use Psr\EventDispatcher\StoppableEventInterface;

class EventManager implements EventManagerInterface
{

    /**
     * The list of listeners
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Subscribe to an event.
     *
     * @param string $event
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function on(string $event, callable $listener, $priority = 0)
    {
        $priority = sprintf('%d.0', $priority);
        if (isset($this->listeners[$priority][$event])
            && in_array($listener, $this->listeners[$priority][$$event], true)
        ) {
            // Duplicate detected
            return;
        }
        $this->listeners[$priority][$event][] = $listener;
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The EventManager that was passed, now modified by listeners.
     */
    public function dispatch(object $event): object
    {
        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            $resultingEvent = call_user_func($listener, $event);

            if ($resultingEvent && $resultingEvent !== $event) {
                throw new \RuntimeException('Hey, the listener did not return the same event object!', 1534141128);
            }
            $event = $resultingEvent ?? $event;
        }

        return $event;
    }

    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $priorities = array_keys($this->listeners);
        usort($priorities, function ($a, $b) {
            return $b <=> $a;
        });

        foreach ($priorities as $priority) {
            foreach ($this->listeners[$priority] as $eventName => $listeners) {
                if ($event instanceof $eventName) {
                    foreach ($listeners as $listener) {
                        yield $listener;
                    }
                }
            }
        }
    }

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
    public function removeListener($event, callable $listener)
    {
        /*
        if (!isset($this->listeners[$event])) {
            return false;
        }
        foreach ($this->listeners[$event] as $index => $check) {
            if ($check === $listener) {
                unset($this->listeners[$eventName][1][$index]);
                unset($this->listeners[$eventName][2][$index]);
                return true;
            }
        }
        return false;
        */
        return true;
    }

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
    public function removeAllListeners(?string $event = null)
    {
        if (!is_null($event)) {
            $priorities = array_keys($this->listeners);
            foreach ($priorities as $priority) {
                unset($this->listeners[$priority][$event]);
            }
        } else {
            $this->listeners = [];
        }
    }
}
