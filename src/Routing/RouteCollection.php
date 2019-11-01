<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13/06/2019
 * Time: 00:31
 */

namespace Keiryo\Routing;

use Symfony\Component\Routing\RouteCollectionBuilder;

class RouteCollection
{

    use RouteBuilderTrait;

    /**
     * @var RouteCollectionBuilder
     */
    private $builder;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string|null
     */
    private $host;

    public function __construct(RouterInterface $router, ?string $host = null)
    {
        /** @var SymfonyRouter $router */
        $this->router = $router;
        $this->builder = new RouteCollectionBuilder($router->getLoader());

        $this->host = $host;
        $this->builder->setHost($host);
    }

    /**
     * @param string|null $host
     * @return RouteCollection
     */
    public function createCollection(?string $host = null): RouteCollection
    {
        return new self($this->router, $host);
    }

    /**
     * @param string $from
     * @param array $options
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     */
    public function import(string $from, array $options = [])
    {
        $options = array_merge([
            'prefix' => '/',
            'format' => 'yaml',
            'host' => $this->host
        ], $options);

        $builder = $this->builder->import($from, $options['prefix'], $options['format']);
        $builder = $builder->setHost($options['host']);
        // TODO: Add support for "name_prefix" option
        unset($options['prefix'], $options['format'], $options['host']);

        foreach ($options as $key => $value) {
            $builder->setDefault("$key", $value);
        }
    }

    /**
     * @return RouteCollectionBuilder
     */
    public function getBuilder(): RouteCollectionBuilder
    {
        return $this->builder;
    }

    /**
     * {@inheritDoc}
     */
    public function match(string $methods, string $path, $controller, ?string $name = null)
    {
        $this->builder
            ->add($path, $controller, $name)
            ->setMethods(explode('|', $methods));
    }

    /**
     * @param string $prefix
     * @param RouteCollection $collection
     */
    public function mount(string $prefix, RouteCollection $collection)
    {
        $this->builder->mount($prefix, $collection->builder);
    }

    /**
     * Proxy
     *
     * @param string $key
     * @param $value
     */
    public function setDefault(string $key, $value)
    {
        $this->builder->setDefault($key, $value);
    }
}
