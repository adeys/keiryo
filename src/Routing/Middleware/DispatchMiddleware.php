<?php

namespace Keiryo\Routing\Middleware;

use League\Container\Argument\ArgumentResolverTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DispatchMiddleware implements MiddlewareInterface, ContainerAwareInterface
{

    use ArgumentResolverTrait, ContainerAwareTrait;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        /** @var \Keiryo\Routing\Route $route */
        $route = $request->attributes->get('_route');
        $controller = $this->resolveController($route->getHandler());

        $reflected = \is_array($controller)
            ? new \ReflectionMethod($controller[0], $controller[1])
            : new \ReflectionFunction($controller);

        $args = $this->reflectArguments($reflected, array_merge($route->getParameters(), compact('request')));

        $result = call_user_func_array($controller, $args);
        $response = $route->getStrategy()->handle($result);

        return $this->validateResponse($response);
    }

    /**
     * Resolve controller into callable
     *
     * @param string|array|callable $controller
     * @return callable
     */
    private function resolveController($controller): callable
    {
        if (is_string($controller)) {
            if (strpos($controller, '::') !== false) {
                $controller = explode('::', $controller);
            } elseif (strpos($controller, ':') !== false) {
                $controller = explode(':', $controller);
            } elseif (method_exists($controller, '__invoke')) {
                $controller = [$this->container->get($controller), '__invoke'];
            }
        }

        if (is_array($controller)) {
            $controller[0] = is_object($controller[0])
                ? $controller[0]
                : $this->container->get($controller[0]);
        }

        return $controller;
    }

    /**
     * Validate controller response
     *
     * @param string|array|Response $response
     * @return Response
     */
    private function validateResponse($response): Response
    {
        if (!$response instanceof Response) {
            throw new \LogicException(sprintf(
                'Controller must return a string, an array or a Response object. "%s" given',
                gettype($response)
            ));
        }

        return $response;
    }
}
