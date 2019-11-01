<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/07/2019
 * Time: 23:38
 */

namespace Keiryo\Plugin\Command;

use Keiryo\Plugin\PluginInterface;
use Keiryo\Plugin\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PluginListCommand extends Command
{

    /**
     * @var PluginManager
     */
    private $manager;

    public function __construct(PluginManager $manager)
    {
        parent::__construct('plugins:list-all');
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('List all of available plugins');
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

        $output->writeln(sprintf('%d available plugin(s)', count($plugins)));
        $style->table(['Name', 'Is loaded'], array_map(function (PluginInterface $plugin) {
            return [
                'name' => $plugin->getName(),
                'loaded' => $this->manager->isLoaded($plugin) ? 'Yes' : 'No'
            ];
        }, $plugins));
    }
}
