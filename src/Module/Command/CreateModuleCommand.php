<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 05/08/2019
 * Time: 08:40
 */

namespace Keiryo\Module\Command;

use Keiryo\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModuleCommand extends Command
{

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * CreateModuleCommand constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        parent::__construct('modules:create');
        $this->configuration = $configuration;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'The module to create')
        ])
            ->setDescription('Create a new module');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = sprintf('%sModule', $input->getArgument('name'));
        $base = $this->configuration->get('root');

        $paths = ['', 'views', 'Controller', 'resources'];
        $base = sprintf('%s/app/%s', $base, $name);
        foreach ($paths as $path) {
            mkdir(sprintf('%s/%s', $base, $path));
        }
        $files = [$name . 'Provider.php', 'resources/routes.yml'];
        foreach ($files as $file) {
            touch(sprintf('%s/%s', $base, $file));
        }

        $class = file_get_contents(__DIR__ . '/stubs/ModuleProvider.stub');
        $class = str_replace('{namespace}', sprintf('%s', $name), $class);
        $class = str_replace('{module}', sprintf('%sProvider', $name), $class);
        $file = new \SplFileObject(sprintf('%s/%sProvider.php', $base, $name), 'w+');
        $file->fwrite($class);

        $output->writeln(sprintf('Module %s successfully created', $name));
    }
}
