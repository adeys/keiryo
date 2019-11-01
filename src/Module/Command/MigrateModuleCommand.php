<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/08/2019
 * Time: 23:38
 */

namespace Keiryo\Module\Command;

use Phinx\Console\PhinxApplication;
use Keiryo\Module\ModuleLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateModuleCommand extends Command
{

    /**
     * @var PhinxApplication
     */
    private $phinx;

    /**
     * @var ModuleLoader
     */
    private $loader;

    public function __construct(ModuleLoader $loader, PhinxApplication $phinx)
    {
        parent::__construct('modules:migrate');
        $this->phinx = $phinx;
        $this->loader = $loader;
    }

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('module', InputArgument::REQUIRED, 'The module to migrate')
        ])
            ->setDescription('Migrate a specific module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('module');
        $module = $this->loader->get($name);
        if ($module && $config = $module->getMigrationsConfig()) {
            $input = new ArrayInput([
                'command' => 'migrate',
                '-c' => $config
            ]);
            return $this->phinx
                ->run($input, $output);
        }

        $output->writeln(sprintf("Module '%s' doesn't exist or doesn't have valid migration config", $module));
        return 0;
    }
}
