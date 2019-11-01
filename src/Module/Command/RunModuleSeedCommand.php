<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08/08/2019
 * Time: 01:09
 */

namespace Keiryo\Module\Command;

use Phinx\Console\PhinxApplication;
use Keiryo\Module\ModuleLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunModuleSeedCommand extends Command
{

    /**
     * @var ModuleLoader
     */
    private $loader;

    /**
     * @var PhinxApplication
     */
    private $phinx;

    public function __construct(ModuleLoader $loader, PhinxApplication $phinx)
    {
        parent::__construct('modules:seed:run');
        $this->loader = $loader;
        $this->phinx = $phinx;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('module', InputArgument::REQUIRED, 'The module to seed')
        ])
            ->setDescription('Run a module seeder');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('module');
        $module = $this->loader->get($name);
        if ($module && $config = $module->getMigrationsConfig()) {
            $input = new ArrayInput([
                'command' => 'seed:run',
                '-c' => $config
            ]);
            return $this->phinx
                ->run($input, $output);
        }

        $output->writeln(sprintf("Module '%s' doesn't exist or doesn't have valid migration config", $module));
        return 0;
    }
}
