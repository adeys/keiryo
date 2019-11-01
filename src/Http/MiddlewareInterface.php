<?php

/**
 * Http Server Request Handler Interface inspired from Psr\Http\ServerRequestHandlerInterface
 *
 * @author K. Raphael <raphalogou@gmail.com>
 */

namespace Keiryo\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareInterface
{

    /**
     * Process an incoming HTTP Request and returns a Response
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response;
}
