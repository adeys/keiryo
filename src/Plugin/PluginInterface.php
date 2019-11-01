<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/06/2019
 * Time: 16:48
 */

namespace Keiryo\Plugin;

use Keiryo\EventManager\EventManagerInterface;

interface PluginInterface
{

    /**
     * Subscribe to events
     *
     * @param EventManagerInterface $eventManager
     * @return mixed
     */
    public function subscribe(EventManagerInterface $eventManager);

    /**
     * Get plugin's short name
     *
     * @return string
     */
    public function getName(): string;
}
