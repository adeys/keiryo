<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22/06/2019
 * Time: 20:24
 */

namespace Keiryo\Events;

trait StoppableEventTrait
{

    /**
     * @var bool
     */
    protected $stoppedPropagation = false;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->stoppedPropagation;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->stoppedPropagation = true;
    }
}
