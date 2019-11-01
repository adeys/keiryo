<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/04/2019
 * Time: 13:01
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseJsonRequestBodyMiddleware implements MiddlewareInterface
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
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $body = json_decode($request->getContent(), true);
            $request->request->replace(is_array($body) ? $body : []);
        }

        return $handler->handle($request);
    }
}
