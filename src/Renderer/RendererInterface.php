<?php
/**
 * Created by PhpStorm.
 * User: K. Raph
 * Date: 01/11/2019
 * Time: 20:11
 */

namespace Keiryo\Renderer;


interface RendererInterface
{

    const DEFAULT = '__main__';

    /**
     * @param string $path
     * @param string|null $namespace
     */
    public function addPath(string $path, string $namespace = RendererInterface::DEFAULT): void;

    /**
     * @param string $name
     * @param $value
     */
    public function addGlobal(string $name, $value): void;

    /**
     * @param string $path
     * @param array $context
     * @return string
     */
    public function render(string $path, array $context = []): string;

}