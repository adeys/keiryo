<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 18:07
 */

namespace Keiryo\Module;

use Psr\Container\ContainerInterface;

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
     * @var array
     */
    private $enabled;

    /**
     * ModuleLoader constructor.
     * @param ContainerInterface $container
     * @param array $enabled
     */
    public function __construct(ContainerInterface $container, array $enabled = [])
    {
        $this->container = $container;
        $this->enabled = $enabled;
    }

    /**
     * @param string[] $modules
     */
    public function load()
    {
        foreach ($this->enabled as $module) {
            if (is_subclass_of($module, ModuleInterface::class)) {
                /** @var ModuleInterface $module */
                $module = new $module();
                $module->register($this->container);
                $this->modules[$module->getName()] = $module;
            }
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
