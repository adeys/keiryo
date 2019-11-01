<?php

namespace Keiryo\Tests\Http;

use PHPUnit\Framework\TestCase;
use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\Pipeline;
use Keiryo\Http\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PipelineTest extends TestCase
{

    /**
     * Pipeline instance
     *
     * @var Pipeline
     */
    private $pipeline;

    public function setUp()
    {
        $this->pipeline = new Pipeline();
        $this->pipeline
            ->pipe($this->decorate(function (Request $request, $handler) {
                $request->attributes->set('app', 'Keiryo');
                return $handler->handle($request);
            }));
    }

    /**
     * Decorate a callable into middleware interface class
     *
     * @param \Closure $callback
     * @return MiddlewareInterface
     */
    private function decorate(\Closure $callback): MiddlewareInterface
    {
        return new class($callback) implements MiddlewareInterface {
            
            /**
             * Decorated callback
             *
             * @var \Closure
             */
            protected $callback;

            public function __construct(\Closure $callback)
            {
                $this->callback = $callback;
            }

            /**
             * {@inheritDoc}
             */
            public function process(Request $request, RequestHandlerInterface $handler): Response
            {
                return call_user_func($this->callback, $request, $handler);
            }
        };
    }

    public function testHandle()
    {
        $this->pipeline
            ->pipe($this->decorate(function (Request $request, $handler) {
                return new Response($request->attributes->get('app'));
            }));

        $request = Request::create('/test');
        $response = $this->pipeline->handle($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertContains('Keiryo', $response->getContent());
    }

    public function testPipelineAsMiddleware()
    {
        $pipeline = new Pipeline();
        $pipeline->pipe($this->decorate(function (Request $request, $handler) {
            $request->attributes->set('pipeline', 'sub-pipe');
            return $handler->handle($request);
        }));
        
        $this->pipeline->pipe($pipeline);
        $this->pipeline
            ->pipe($this->decorate(function (Request $request, $handler) {
                $content = sprintf('app: %s , pipeline: %s', $request->attributes->get('app'), $request->attributes->get('pipeline', 'primary'));
                return new Response($content);
            }));
        
        $request = Request::create('/test');
        $response = $this->pipeline->handle($request);
    
        $this->assertInstanceOf(Response::class, $response);
        $this->assertContains('app: Keiryo , pipeline: sub-pipe', $response->getContent());
    }
}
