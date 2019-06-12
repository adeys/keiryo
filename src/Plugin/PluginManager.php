<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 16:47
 */

namespace Simplex\Plugin;


use Psr\Container\ContainerInterface;
use Simplex\Event\EventManagerInterface;

class PluginManager
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PluginInterface[]
     */
    private $loaded = [];

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * PluginManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->eventManager = $container->get(EventManagerInterface::class);

    }

    /**
     * Loads a set of plugins
     *
     * @param array $plugins
     */
    public function load(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if (is_string($plugin) && class_exists($plugin)) {
                $plugin = $this->container->get($plugin);
            }

            if ($plugin instanceof PluginInterface && !$this->isLoaded($plugin)) {
                $this->add($plugin);
            }
        }
    }

    /**
     * Checks if a plugin has already been loaded
     *
     * @param PluginInterface $plugin
     * @return bool
     */
    public function isLoaded(PluginInterface $plugin)
    {
        return array_key_exists($plugin->getName(), $this->loaded);
    }

    /**
     * Add a plugin
     *
     * @param PluginInterface $plugin
     */
    public function add(PluginInterface $plugin)
    {
        $plugin->subscribe($this->eventManager);
        $this->loaded[$plugin->getName()] = $plugin;
    }
}