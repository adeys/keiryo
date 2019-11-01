<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/07/2019
 * Time: 23:44
 */

namespace Keiryo\Plugin\Command;

use Keiryo\Plugin\PluginInterface;
use Keiryo\Plugin\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PluginLoadedListCommand extends Command
{

    /**
     * @var PluginManager
     */
    private $manager;

    public function __construct(PluginManager $manager)
    {
        parent::__construct('plugins:list-loaded');
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('List all loaded plugins');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugins = $this->manager->all();
        $style = new SymfonyStyle($input, $output);

        $output->writeln(sprintf('%d loaded plugin(s)', count($plugins)));
        $style->table(['Name'], array_map(function (PluginInterface $plugin) {
            return [
                'name' => $plugin->getName()
            ];
        }, $plugins));
    }
}
