<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/03/2019
 * Time: 12:15
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Keiryo\Security\Authentication\StatelessAuthenticationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuthenticationMiddleware implements MiddlewareInterface
{

    /**
     * @var StatelessAuthenticationManager
     */
    private $manager;

    public function __construct(StatelessAuthenticationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Process an incoming HTTP Request and returns a Response
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if ($this->manager->authenticate($request)) {
            $request->attributes->set('api_token', $this->manager->getUser()->getToken());

            return $handler->handle($request);
        }

        return new JsonResponse(['status' => 401, 'message' => 'API Token authentication failed'], 401);
    }
}
