<?php

namespace Keiryo\Tests;

use Keiryo\Kernel;
use Keiryo\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;

class KernelForTest extends Kernel
{
    public function __construct()
    {
        parent::__construct();
        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        /** @var RouterInterface */
        $router = $this->container->get(RouterInterface::class);
        $router->match('GET', '/test', function () {
            return new Response('Hello test as Response');
        });
        $router->match('GET', '/test-string', function () {
            return 'Hello test as string';
        });
        $router->match('GET', '/test-array', function () {
            return ['Hello', 'Test', 'As', 'Array'];
        });
        $router->match('GET', '/test-object', function () {
            return new \stdClass();
        });
    }
}
