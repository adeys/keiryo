<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 18:07
 */

namespace Keiryo\Module;

use Keiryo\Configuration\Configuration;
use Keiryo\Renderer\TwigRenderer;
use Keiryo\Routing\RouteCollection;

interface ModuleInterface
{
    /**
     * Get module short name for searching in config
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Register configuration
     *
     * @param Configuration $configuration
     * @return mixed
     */
    public function configure(Configuration $configuration);

    /**
     * Get entity maps provided by the module
     *
     * @return array
     */
    public function getMappings(): array;

    /**
     * Register backend routes
     *
     * @param RouteCollection $collection
     * @return void
     */
    public function getAdminRoutes(RouteCollection $collection): void;

    /**
     * Register frontend routes
     *
     * @param RouteCollection $collection
     */
    public function getSiteRoutes(RouteCollection $collection): void;

    /**
     * Register view templates
     *
     * @param TwigRenderer $renderer
     * @return void
     */
    public function registerTemplates(TwigRenderer $renderer);

    /**
     * Get provided commands to add to console
     *
     * @return array
     */
    public function getCommands(): array;

    /**
     * Gets migrations config file to use
     *
     * @return null|string
     */
    public function getMigrationsConfig(): ?string;
}
