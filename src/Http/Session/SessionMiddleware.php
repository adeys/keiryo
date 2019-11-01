<?php

namespace Keiryo\Http\Session;

use Keiryo\Http\CookieStorage;
use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionMiddleware implements MiddlewareInterface
{

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var CookieStorage
     */
    private $cookieStorage;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     * @param CookieStorage $cookieStorage
     */
    public function __construct(SessionInterface $session, CookieStorage $cookieStorage)
    {
        $this->session = $session;
        $this->cookieStorage = $cookieStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $this->session->start();
        $request->setSession($this->session);
        $this->cookieStorage->setCookie($this->session->getName(), $this->session->getId());
        return $handler->handle($request);
    }
}
