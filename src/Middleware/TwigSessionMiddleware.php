<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 20:24
 */

namespace Keiryo\Middleware;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Keiryo\Renderer\TwigRenderer;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class TwigSessionMiddleware implements MiddlewareInterface
{

    /**
     * @var TwigRenderer
     */
    private $renderer;

    /**
     * TwigSessionMiddleware constructor.
     * @param TwigRenderer $renderer
     */
    public function __construct(TwigRenderer $renderer)
    {
        $this->renderer = $renderer;
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
        if ($request->hasSession()) {
            /** @var Session $session */
            $env = $this->renderer->getEnv();
            $session = $request->getSession();
            $flash = $session->getFlashBag();

            $env->addGlobal('session', $session);
            $env->addGlobal('flash', $flash);
            $env->addGlobal('errors', new ParameterBag($flash->get('errors', [])));
        }

        return $handler->handle($request);
    }
}
