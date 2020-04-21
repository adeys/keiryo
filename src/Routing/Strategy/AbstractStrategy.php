<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 19:13
 */

namespace Keiryo\Routing\Strategy;

use Keiryo\Http\MiddlewareInterface;
use Keiryo\Http\Pipeline;
use Keiryo\Http\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractStrategy implements StrategyInterface
{

    /**
     * @var array
     */
    protected $middlewares = [];
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Pipeline
     */
    private $pipeline;

    /**
     * AbstractStrategy constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pipeline = $container->get(Pipeline::class);
    }

    /**
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $this->pipeline->seed($this->middlewares, function ($middleware) {
            if (is_string($middleware)) {
                return $this->container->get($middleware);
            } elseif (is_object($middleware) && $middleware instanceof MiddlewareInterface) {
                return $middleware;
            }
            return null;
        });

        return $this->pipeline->process($request, $handler);
    }

    /**
     * @param $response Response|mixed
     * @return Response
     */
    public function handle($response): Response
    {
        $response = $response instanceof Response
            ? $response
            : $this->createResponse($response);

        if (!$response) {
            throw new \Exception(sprintf(
                'Unable to build a proper response. Got response of type: "%s"',
                gettype($response)
            ));
        }

        return $response;
    }

    /**
     * @param MiddlewareInterface|string $middleware
     */
    public function add(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Create a valid response based on strategy
     *
     * @param $result Response|mixed
     *
     * @return Response|null
     */
    abstract protected function createResponse($result): ?Response;
}
