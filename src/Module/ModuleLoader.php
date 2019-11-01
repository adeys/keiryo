<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 18:07
 */

namespace Keiryo\Module;

use Psr\Container\ContainerInterface;
use Keiryo\Configuration\Configuration;
use Keiryo\DataMapper\DataMapperServiceProvider;
use Keiryo\DataMapper\EntityManager;
use Keiryo\DataMapper\Mapping\MappingRegistry;
use Keiryo\Middleware\AuthenticationMiddleware;
use Keiryo\Renderer\TwigRenderer;
use Keiryo\Renderer\TwigServiceProvider;
use Keiryo\Routing\RouterInterface;
use Keiryo\Routing\SymfonyRouter;

class ModuleLoader
{

    /**
     * @var ModuleInterface[]
     */
    private $modules = [];
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ModuleLoader constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string[] $modules
     */
    public function load(array $modules)
    {
        foreach ($modules as $module) {
            if (is_subclass_of($module, ModuleInterface::class)) {
                $module = $this->container->get($module);
                $this->modules[$module->getName()] = $module;
            }
        }

        $this->bootstrap();
    }

    /**
     * Bootstrap the modules
     */
    public function bootstrap()
    {
        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);

        /** @var TwigRenderer $twig */
        $twig = in_array(TwigServiceProvider::class, $config->get('providers', []))
            ? $this->container->get(TwigRenderer::class)
            : null;

        /** @var SymfonyRouter $router */
        $router = $this->container->get(RouterInterface::class);
        $admin = $router->createCollection();
        $admin->setDefault('_middlewares', [AuthenticationMiddleware::class]);

        $mappings = [];
        foreach ($this->modules as $module) {
            // Register configuration data
            $module->configure($config);

            // Register routes for front end back end
            $builder = $router->createCollection();
            $module->getAdminRoutes($admin);
            $module->getSiteRoutes($builder);
            $router->mount('/', $builder);

            //Register view bindings
            if ($twig) {
                $module->registerTemplates($twig);
            }

            // Register mappings
            array_push($mappings, $module->getMappings());
        }
        $router->mount('/admin', $admin);

        $map = class_exists(EntityManager::class) &&
            in_array(DataMapperServiceProvider::class, $config->get('providers', []));
        $this->loadMappings($map, $mappings, $config->get('database.default', null));
    }

    /**
     * @param bool $registered
     * @param array $mappings
     * @param string|null $defaultConnection
     */
    public function loadMappings(bool $registered, array $mappings, ?string $defaultConnection)
    {
        if ($registered) {
            $registry = new MappingRegistry();

            foreach ($mappings as $mapping) {
                if (isset($mapping['mappings'])) {
                    $registry->register($mapping['mappings'], $mapping['connection'] ?? $defaultConnection);
                }
            }

            $this->container->add(MappingRegistry::class, $registry);
        }
    }

    /**
     * @return ModuleInterface[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    public function get(string $name): ?ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }
}
