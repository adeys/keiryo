<?php

namespace Keiryo\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Keiryo\Renderer\TwigRenderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRendererTest extends TestCase
{

    public function setUp()
    {
        $loader = new FilesystemLoader();
        $this->renderer = new TwigRenderer(new Environment($loader), $loader);
        $this->renderer->addPath(__DIR__);
    }

    public function testRender()
    {
        $page = $this->renderer->render('index', ['name' => 'Twig']);
        $this->assertEquals('Hello Twig', $page);
    }
    
    public function testRenderNamespaced()
    {
        $this->renderer->addPath(__DIR__.'/first', "first");
        $this->renderer->addPath(__DIR__.'/second', "second");

        $page = $this->renderer->render('@first/index', ['name' => 'Twig']);
        $this->assertEquals('Hello first Twig', $page);
        
        $page = $this->renderer->render('@second/index', ['name' => 'Twig']);
        $this->assertEquals('Hello second Twig', $page);
    }
}
