<?php

namespace Keiryo\Routing;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Keiryo\Configuration\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

class RoutingServiceProvider extends AbstractServiceProvider
{

    /**
     * {@inheritDoc}
     */
    protected $provides = [
        RouterInterface::class
    ];

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $config = $this->container->get(Configuration::class);

        $loader = new YamlFileLoader(new FileLocator());
        $router = new SymfonyRouter($loader, $config->get('app_host', 'localhost'));

        // Register strategies on router
        // By registering strategy resolver instead of strategy themselves we remove unnecessary classes' call
        foreach ($config->get('routing.strategies') as $name => $strategy) {
            $router->addStrategy($name, function () use ($strategy) {
                return $this->container->get($strategy);
            });
        }
        $router->setStrategy('web');

        $router->get('/', 'Keiryo\Routing\RoutingServiceProvider:default', 'home');

        $this->container->add(RouterInterface::class, $router);
    }

    public function default()
    {
        return <<<HTML
<div style='text-align: center; font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif; font-size: x-large'>
    <h1>Welcome to Keiryo default home</h1>
</div>
HTML;
    }
}
