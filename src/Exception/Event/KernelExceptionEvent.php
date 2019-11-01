<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 17:58
 */

namespace Keiryo\Exception\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Keiryo\Events\StoppableEventTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KernelExceptionEvent implements StoppableEventInterface
{

    use StoppableEventTrait;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Request
     */
    private $request;

    public function __construct(\Exception $exception, Request $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Checks response
     *
     * @return bool
     */
    public function hasResponse(): bool
    {
        return null !== $this->response;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
