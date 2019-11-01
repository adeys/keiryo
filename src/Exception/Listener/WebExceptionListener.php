<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 18:14
 */

namespace Keiryo\Exception\Listener;

use Keiryo\Database\Exceptions\ResourceNotFoundException as DatabaseResourceNotFoundException;
use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Exception\Event\HttpExceptionEvent;
use Keiryo\Exception\Event\KernelExceptionEvent;
use Keiryo\Security\Authentication\Authorization\AuthorizationException;
use Keiryo\Security\Authentication\Authorization\AuthorizationManager;
use Keiryo\Security\Csrf\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class WebExceptionListener
{

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * WebExceptionListener constructor.
     * @param EventManagerInterface $eventManager
     */
    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @param KernelExceptionEvent $event
     * @return KernelExceptionEvent
     * @throws \Exception
     */
    public function handle(KernelExceptionEvent $event): KernelExceptionEvent
    {
        $exception = $event->getException();

        switch (true) {
            case $exception instanceof DatabaseResourceNotFoundException:
            case $exception instanceof ResourceNotFoundException:
                $response = $this->handleNotFoundException($exception);
                break;
            case $exception instanceof TokenMismatchException:
                $response = $this->handleTokenMismatchException($exception);
                break;
            case $exception instanceof AuthorizationManager:
                $response = $this->handleAuthorizationException($exception);
                break;
            case $exception instanceof MethodNotAllowedException:
                $response = $this->handleMethodNotAllowedException($exception);
                break;
            default:
                throw $exception;
        }

        $event->setResponse($response);
        return $event;
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    private function handleNotFoundException(\Exception $exception): Response
    {
        $httpEvent = $this->eventManager->dispatch(new HttpExceptionEvent($exception, 404, [
            'title' => 'Page Not Found',
            'content' => 'Sorry, the page you are looking for could not be found.'
        ]));
        if ($httpEvent->hasResponse()) {
            $response = $httpEvent->getResponse();
        } else {
            $response = new Response();
            $response->setContent("<title>404 Not Found</title> <h1>Not Found</h1>" . $exception->getMessage());
            $response->setStatusCode($exception->getCode());
        }

        return $response;
    }

    /**
     * @param TokenMismatchException $exception
     * @return Response
     */
    private function handleTokenMismatchException(TokenMismatchException $exception): Response
    {
        $httpEvent = $this->eventManager->dispatch(new HttpExceptionEvent($exception, 419, [
            'title' => 'Session expired',
            'content' => 'Sorry, your session has expired. Please refresh and try again.'
        ]));
        if ($httpEvent->hasResponse()) {
            $response = $httpEvent->getResponse();
        } else {
            $response = new Response();
            $response->setContent("<title>419 Session Expired</title> <h1>Sorry, your session has expired.</h1>" . $exception->getMessage());
            $response->setStatusCode($exception->getCode());
        }

        return $response;
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return Response
     */
    private function handleMethodNotAllowedException(MethodNotAllowedException $exception): Response
    {
        return new Response(
            $exception->getMessage(),
            405,
            ['Allow' => implode(', ', $exception->getAllowedMethods())]
        );
    }

    /**
     * @param AuthorizationException $exception
     * @return Response
     */
    private function handleAuthorizationException(AuthorizationException $exception): Response
    {
        $response = new Response();
        $response->setContent("<title>403 Forbidden</title> <h1>Sorry, you are forbidden from accessing this page.</h1>" . $exception->getMessage());
        $response->setStatusCode($exception->getCode());

        return $response;
    }
}
