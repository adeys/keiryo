<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 04:20
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Keiryo\Security\Authentication\AuthenticationManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{

    /**
     * @var AuthenticationManager
     */
    private $authenticationManager;

    /**
     * AuthenticationMiddleware constructor.
     * @param AuthenticationManager $authenticationManager
     */
    public function __construct(AuthenticationManager $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
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
        if ($this->authenticationManager->authenticate($request)) {
            return $handler->handle($request);
        }

        if ($request->hasSession()) {
            $request->getSession()->set('auth.referer', $request->getUri());
        }

        return new RedirectResponse(sprintf(
            '%s://%s',
            $request->getScheme(),
            $this->authenticationManager->getLoginPath()
        ));
    }
}
