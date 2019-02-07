<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 19:54
 */

namespace App\JobeetModule;

use Simplex\Module\AbstractModule;
use Simplex\Renderer\TwigRenderer;
use Simplex\Routing\RouterInterface;
use Twig\TwigFilter;

class JobeetServiceProvider extends AbstractModule
{

    /**
     * JobeetServiceProvider constructor.
     * @param RouterInterface $router
     * @param TwigRenderer $renderer
     * @throws \Twig_Error_Loader
     */
    public function __construct(RouterInterface $router, TwigRenderer $renderer)
    {
        $renderer->addPath(__DIR__ . '/views', 'jobeet');

        $renderer->getEnv()->addFilter(new TwigFilter('slug', function (string $string) {
            $string = preg_replace('~\W+~', '-', $string);
            $string = trim($string, '-');

            return strtolower($string);
        }));

        $router->import(__DIR__ . '/resources/routes.yml', ['prefix' => 'jobeet']);
    }

    /**
     * Get module short name for searching in config
     *
     * @return string
     */
    public function getName(): string
    {
        return 'jobeet';
    }
}