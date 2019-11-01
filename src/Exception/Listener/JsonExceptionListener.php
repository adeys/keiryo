<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/04/2019
 * Time: 12:36
 */

namespace Keiryo\Exception\Listener;

use Keiryo\Database\Exceptions\ResourceNotFoundException;
use Keiryo\Exception\Event\KernelExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class JsonExceptionListener
{

    /**
     * Process an incoming HTTP Request and returns a Response
     *
     * @param KernelExceptionEvent $event
     * @return KernelExceptionEvent
     */
    public function handle(KernelExceptionEvent $event): KernelExceptionEvent
    {
        if (0 !== strpos($event->getRequest()->headers->get('Accept'), 'application/json') ||
            0 !== strpos($event->getRequest()->headers->get('Accept'), 'application/json')
        ) {
            return $event;
        }

        $exception = $event->getException();

        switch (true) {
            case $exception instanceof \Symfony\Component\Routing\Exception\ResourceNotFoundException:
            case $exception instanceof ResourceNotFoundException:
                $response = new JsonResponse([
                    'code' => 404,
                    'message' => 'Resource not found.'
                ], 404);
                break;
            case $exception instanceof MethodNotAllowedException:
                $response = new JsonResponse(
                    [
                    'code' => 405,
                    'message' => $exception->getMessage()
                    ],
                    405,
                    ['Allow' => implode(', ', $exception->getAllowedMethods())]
                );
                break;
            default:
                $response = new JsonResponse([
                    'code' => 500,
                    'message' => 'Sorry! An unexpected error where encountered.'
                ], 500);
                break;
        }

        $event->setResponse($response);
        $event->stopPropagation();
        return $event;
    }
}
