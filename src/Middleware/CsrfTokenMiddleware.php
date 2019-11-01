<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 22:40
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Keiryo\Security\Csrf\CsrfTokenManager;
use Keiryo\Security\Csrf\TokenMismatchException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CsrfTokenMiddleware implements MiddlewareInterface
{

    /**
     * @var CsrfTokenManager
     */
    private $tokenManager;

    /**
     * CsrfTokenMiddleware constructor.
     * @param CsrfTokenManager $tokenManager
     */
    public function __construct(CsrfTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
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
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']) ||
            $this->isValid($request)
        ) {
            $token = $this->tokenManager->generateToken();
            $request->getSession()->set(CsrfTokenManager::TOKEN_NAME, $token);

            return $handler->handle($request);
        }

        throw new TokenMismatchException('Sorry, your session has expired. Please refresh and try again.', 419);
    }

    /**
     * Checks request validity
     *
     * @param Request $request
     * @return bool
     */
    protected function isValid(Request $request): bool
    {
        $this->tokenManager->validate($request);

        return $this->tokenManager->isValid();
    }
}
