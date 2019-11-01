<?php

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrailingSlashMiddleware implements MiddlewareInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $uri = $request->getPathInfo();
        if ($uri != '/' && $uri[-1] === '/') {
            return new RedirectResponse(rtrim($uri, '/')/* substr($uri, 0, strlen($uri)-1)*/);
        }

        return $handler->handle($request);
    }
}
