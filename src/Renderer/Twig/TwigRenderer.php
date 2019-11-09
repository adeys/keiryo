<?php

namespace Keiryo\Renderer\Twig;

use Keiryo\Renderer\RendererInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer implements RendererInterface
{

    /**
     * Twig environment
     *
     * @var Environment
     */
    private $twig;

    /**
     * Template loader
     *
     * @var FilesystemLoader
     */
    private $loader;

    /**
     * Constructor
     *
     * @param Environment $twig
     * @param FilesystemLoader $loader
     */
    public function __construct(Environment $twig, FilesystemLoader $loader)
    {
        $this->loader = $loader;
        $this->twig = $twig;
    }

    /**
     * Add a template path
     *
     * @param string $path
     * @param string $namespace
     * @return void
     * @throws \Twig_Error_Loader
     */
    public function addPath(string $path, string $namespace = self::DEFAULT): void
    {
        $this->loader->addPath($path, $namespace);
    }

    /**
     * Render a template
     *
     * @param string $file
     * @param array $params
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(string $file, array $params = []): string
    {
        return $this->twig->render("$file.twig", $params);
    }

    /**
     * @return Environment
     */
    public function getEnv(): Environment
    {
        return $this->twig;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function addGlobal(string $name, $value): void
    {
        $this->twig->addGlobal($name, $value);
    }
}
