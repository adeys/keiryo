<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 16/06/2019
 * Time: 19:44
 */

namespace Keiryo\Plugin;

use Composer\Autoload\ClassLoader;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Container\ContainerInterface;
use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Events\KernelBootEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class PluginServiceProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        PluginManager::class
    ];

    /**
     * @var PluginManager
     */
    private $plugins;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->plugins = new PluginManager($container);
        $container->get(EventManagerInterface::class)
            ->on(KernelBootEvent::class, [$this, 'bootstrap']);
    }

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->add(PluginManager::class, $this->plugins);
    }

    /**
     * @param KernelBootEvent $event
     * @return KernelBootEvent
     * @throws \Exception
     */
    public function bootstrap(KernelBootEvent $event): KernelBootEvent
    {
        $configuration = $event->getConfiguration();
        try {
            $config = $configuration->get('plugins.enabled', []);
            $root = $configuration->get('root');
            /** @var ClassLoader $composer */
            $composer = require $root . '/vendor/autoload.php';
            $enabled = [];

            $plugins = (new Finder())->in($root . '/plugins/*')
                ->name('metadata.yml')
                ->files()
                ->getIterator();

            foreach ($plugins as $plugin) {
                $metadata = Yaml::parseFile($plugin);
                $dir = dirname($plugin);
                $short = $metadata['name'] ?? $dir;
                $this->plugins->register($short, [
                    'name' => $metadata['fullname'],
                    'description' => 'Description'
                ]);

                // Load the plugin only if it has been enabled
                if (in_array($short, $config)) {
                    $autoload = $metadata['autoload'] ?? [];
                    $plugin = $metadata['provider'];
                    foreach ($autoload as $path => $namespace) {
                        $composer->addPsr4(rtrim($namespace, '\\') . '\\', $dir . '/' . $path);
                    }

                    $enabled[] = $plugin;
                }
            }

            $this->plugins->load($enabled);
        } catch (\InvalidArgumentException $_) {
        } catch (\Exception $e) {
            throw $e;
        }

        return $event;
    }
}
