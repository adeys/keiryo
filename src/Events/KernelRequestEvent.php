<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22/06/2019
 * Time: 20:23
 */

namespace Keiryo\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KernelRequestEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
        $this->stopPropagation();
    }
}
