<?php

namespace Keiryo\Renderer;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Keiryo\Configuration\Configuration;
use Keiryo\EventManager\EventManagerInterface;
use Keiryo\Exception\Event\HttpExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider extends AbstractServiceProvider
{

    /**
     * {@inheritDoc}
     */
    protected $provides = [
        TwigRenderer::class
    ];

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->container->add(
            TwigRenderer::class,
            function () {
                $loader = new FilesystemLoader();

                $config = $this->container
                    ->get(Configuration::class)
                    ->get('view.twig');

                $loader->addPath($config['path']);
                $twig = new Environment($loader, ['cache' => $config['cache'] ?? false]);

                foreach ($config['extensions'] ?? [] as $extension) {
                    $twig->addExtension($this->container->get($extension));
                }

                return new TwigRenderer($twig, $loader);
            }
        );

        $this->container->get(EventManagerInterface::class)
            ->on(HttpExceptionEvent::class, function (HttpExceptionEvent $event) {
                $response = new Response();
                $twig = $this->container->get(TwigRenderer::class);
                $response->setContent($twig->render('errors/4xx', $event->getViewData()));
                $response->setStatusCode($event->getStatusCode());

                $event->setResponse($response);
                return $event;
            });
    }
}
