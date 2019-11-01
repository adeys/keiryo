<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/07/2019
 * Time: 03:39
 */

namespace Keiryo\Module\Command;

use Keiryo\Module\ModuleInterface;
use Keiryo\Module\ModuleLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ModuleListCommand extends Command
{

    /**
     * @var ModuleLoader
     */
    private $loader;

    public function __construct(ModuleLoader $loader)
    {
        parent::__construct('modules:list');
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('List all of loaded modules');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = $this->loader->getModules();
        $style = new SymfonyStyle($input, $output);

        $output->writeln('Loaded modules');
        $style->table(['Name', 'Class'], array_map(function (ModuleInterface $module) {
            return [
                'name' => $module->getName(),
                'class' => get_class($module)
            ];
        }, $modules));
    }
}
