<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 27/01/2019
 * Time: 18:35
 */

namespace Keiryo\Routing\Extensions;

use Keiryo\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigRouterExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('route', [$this, 'route'])
        ];
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public function route(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters);
    }
}
