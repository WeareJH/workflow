<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push extends Command implements CommandInterface
{
    use DockerAwareTrait;
    use ProcessRunnerTrait;

    public function __construct(ProcessBuilder $processBuilder)
    {
        parent::__construct();
        $this->processBuilder = $processBuilder;
    }

    public function configure()
{
    $this
        ->setName('push')
        ->setDescription('Push files from host to the container')
        ->addArgument(
            'files',
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Files to push, relative to project root'
        );
}

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->phpContainerName();
        $files     = is_array($input->getArgument('files'))
            ? $input->getArgument('files')
            : [$input->getArgument('files')];

        foreach ($files as $file) {
            $srcPath  = trim($file, '/');
            $destPath = trim(str_replace(basename($srcPath), '', $srcPath), '/');

            if (!file_exists($srcPath)) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $srcPath));
                return;
            }

            $command = sprintf('docker cp %s %s:/var/www/%s', $srcPath, $container, $destPath);
            $this->runProcessShowingErrors($output, explode(' ', $command));

            $output->writeln(
                sprintf("<info> + %s > %s </info>", $srcPath, $container)
            );
        }
    }
}
