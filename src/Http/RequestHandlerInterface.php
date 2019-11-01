<?php

/**
 * Http Server Request Handler Interface inspired from Psr\Http\ServerRequestHandlerInterface
 *
 * @author K. Raphael <raphalogou@gmail.com>
 */

namespace Keiryo\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestHandlerInterface
{

    /**
     * Handle an incoming HTTP Request
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response;
}
