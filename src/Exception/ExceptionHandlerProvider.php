<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 19:23
 */

namespace Keiryo\Exception;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Exception\Event\KernelExceptionEvent;
use Keiryo\Exception\Listener\JsonExceptionListener;
use Keiryo\Exception\Listener\WebExceptionListener;

class ExceptionHandlerProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function boot()
    {
        /** @var EventManagerInterface $eventManager */
        $eventManager = $this->container->get(EventManagerInterface::class);

        $eventManager->on(KernelExceptionEvent::class, function (KernelExceptionEvent $event) {
            return (new JsonExceptionListener())->handle($event);
        }, 100);
        $eventManager->on(KernelExceptionEvent::class, function (KernelExceptionEvent $event) use ($eventManager) {
            return (new WebExceptionListener($eventManager))->handle($event);
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}
