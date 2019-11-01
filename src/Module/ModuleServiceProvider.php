<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 05/05/2019
 * Time: 12:03
 */

namespace Keiryo\Module;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Container\ContainerInterface;
use Keiryo\Configuration\Configuration;
use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Events\KernelBootEvent;
use Keiryo\Module\Command\CreateModuleCommand;
use Keiryo\Module\Command\CreateModuleMigrationCommand;
use Keiryo\Module\Command\CreateModuleSeedCommand;
use Keiryo\Module\Command\MigrateModuleCommand;
use Keiryo\Module\Command\ModuleListCommand;
use Keiryo\Module\Command\RunModuleSeedCommand;

class ModuleServiceProvider extends AbstractServiceProvider
{

    protected $provides = [
        ModuleLoader::class
    ];

    /**
     * @var ModuleLoader
     */
    private $loader;

    /**
     * ModuleServiceProvider constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);

        // Load modules
        $modules = $config->get('modules', []);
        $loader = new ModuleLoader($this->container);
        $loader->load($modules);
        $this->loader = $loader;

        // Load commands on bootstrap
        $this->container
            ->get(EventManagerInterface::class)
            ->on(KernelBootEvent::class, function (KernelBootEvent $event) {
                $event->getConfiguration()->add('console.commands', [
                    ModuleListCommand::class,
                    CreateModuleCommand::class,
                    MigrateModuleCommand::class,
                    CreateModuleMigrationCommand::class,
                    CreateModuleSeedCommand::class,
                    RunModuleSeedCommand::class
                ]);
                return $event;
            });
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
        $this->container->add(ModuleLoader::class, $this->loader);
    }
}
