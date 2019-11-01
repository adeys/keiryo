<?php

namespace Keiryo\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Keiryo\Routing\Route;
use Keiryo\Routing\SymfonyRouter;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class SymfonyRouterTest extends TestCase
{
    public function setUp()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->router = new SymfonyRouter($loader);
        $this->router->get('/test', 'controller', 'test');
        $this->router->match('GET|POST', '/hello/{name}', 'controller', 'hello');
    }

    public function testDispatch()
    {
        $request = Request::create('/test');
        $result = $this->router->dispatch($request);
        $this->assertInstanceOf(Route::class, $result);
        $this->assertEquals('controller', $result->getHandler());
        $this->assertEquals([], $result->getParameters());
    }
    
    public function testDispatchWithDynamicParts()
    {
        $request = Request::create('/hello/world');
        $result = $this->router->dispatch($request);
        $this->assertInstanceOf(Route::class, $result);
        $this->assertEquals('controller', $result->getHandler());
        $this->assertEquals(['name' => 'world'], $result->getParameters());
    }

    public function testDispatchUndefinedRoute()
    {
        $request = Request::create('/undefined');
        $this->expectException(ResourceNotFoundException::class);
        $this->router->dispatch($request);
    }

    public function testGenerateRoute()
    {
        $route = $this->router->generate('hello', ['name' => 'Keiryo']);

        $this->assertEquals('/hello/Keiryo', $route);
    }
}
