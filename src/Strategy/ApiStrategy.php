<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/03/2019
 * Time: 12:02
 */

namespace Keiryo\Strategy;

use Psr\Container\ContainerInterface;
use Keiryo\Configuration\Configuration;
use Keiryo\Routing\Middleware\AbstractStrategy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiStrategy extends AbstractStrategy
{

    /**
     * Api Strategy constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->middlewares = $container->get(Configuration::class)
            ->get('routing.middlewares.api', []);
    }

    /**
     * Create a valid response based on strategy
     *
     * @param $response Response|mixed
     *
     * @return Response|null
     */
    protected function createResponse($response): ?Response
    {
        if (is_array($response) ||
            (is_object($response) && $response instanceof \ArrayAccess)
        ) {
            return new JsonResponse((array)$response);
        }

        return null;
    }
}
