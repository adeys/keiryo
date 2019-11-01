<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 18:07
 */

namespace Keiryo\Module;

use Psr\Container\ContainerInterface;

interface ModuleInterface
{
    /**
     * Get module short name for searching in config
     *
     * @return string
     */
    public function getName(): string;

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function register(ContainerInterface $container);
}
