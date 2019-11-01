<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 23:48
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OverrideRequestMethodMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming HTTP Request and returns a Response
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if ('POST' === $request->getMethod()) {
            if ($method = $request->request->get('_method')) {
                $request->setMethod(strtoupper($method));
            }
        }

        return $handler->handle($request);
    }
}
