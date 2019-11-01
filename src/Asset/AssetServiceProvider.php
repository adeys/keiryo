<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28/07/2019
 * Time: 18:55
 */

namespace Keiryo\Asset;

use League\Container\ServiceProvider\AbstractServiceProvider;

class AssetServiceProvider extends AbstractServiceProvider
{

    protected $provides = [
        AssetManager::class
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->add(AssetManager::class, function () {
            $manager = new AssetManager('/');

            $manager->register('/css', 'css');
            $manager->register('/img', 'img');
            $manager->register('/js', 'js');

            return $manager;
        });
    }
}
