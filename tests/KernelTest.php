<?php

namespace Keiryo\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KernelTest extends TestCase
{
    public function setUp()
    {
        $this->kernel = new KernelForTest;
    }

    public function testHandleWithControllerReturnResponse()
    {
        $response = $this->kernel->handle(Request::create('/test'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Hello test as Response', $response->getContent());
    }

    public function testHandleWithControllerReturnString()
    {
        $response = $this->kernel->handle(Request::create('/test-string'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Hello test as string', $response->getContent());
    }

    public function testHandleWithControllerReturnArray()
    {
        $response = $this->kernel->handle(Request::create('/test-array'));
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode(['Hello', 'Test', 'As', 'Array']), $response->getContent());
    }

    public function testHandleWithControllerReturnObjectThrowsException()
    {
        $this->expectException(\LogicException::class);
        $response = $this->kernel->handle(Request::create('/test-object'));
    }
}
