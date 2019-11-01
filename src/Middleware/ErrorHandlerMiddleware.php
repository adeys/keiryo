<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 18:39
 */

namespace Keiryo\Middleware;

use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Exception\Event\KernelExceptionEvent;
use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * ErrorHandlerMiddleware constructor.
     * @param EventManagerInterface $eventManager
     */
    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Process an incoming HTTP Request and returns a Response
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $exception) {
            $event = $this->eventManager->dispatch(new KernelExceptionEvent($exception, $request));

            if (!$event->hasResponse()) {
                throw $exception;
            }

            return $event->getResponse();
        }
    }
}
