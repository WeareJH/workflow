<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Push extends Command implements CommandInterface
{
    use DockerAwareTrait;

    /**
     * @var Files
     */
    private $files;

    public function __construct(Files $files)
    {
        parent::__construct();
        $this->files = $files;
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
            )
            ->addOption('no-overwrite', 'o', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $overwrite = !$input->getOption('no-overwrite');
        $container = $this->phpContainerName();
        $files     = (array) $input->getArgument('files');


        foreach ($files as $file) {
            if (!file_exists($file)) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $file));
                return;
            }

            if ($overwrite && is_dir($file) && $this->files->existsInContainer($container, $file)) {
                //we only remove on container first if it is a directory
                //as the new directory we push may have a different set of files in it
                //for files we can just overwrite and save some cycles
                $this->files->delete($container, [$file]);
            }
        }

        $this->files->upload($container, $files);
    }
}
