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
class Pull extends Command implements CommandInterface
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
        $help  = "Pull files from the docker environment to the host, Useful for pulling vendor etc\n\n";
        $help .= 'If the watch is running and you pull a file that is being watched it will ';
        $help .= "automatically be pushed back into the container\n";
        $help .= 'If this is not what you want (large dirs can cause issues here) stop the watch, ';
        $help .= 'pull then start the watch again afterwards';

        $this
            ->setName('pull')
            ->setDescription('Pull files from the docker environment to the host')
            ->setHelp($help)
            ->addArgument(
                'files',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Files to pull, relative to project root'
            )
            ->addOption('no-overwrite', 'o', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $overwrite = !$input->getOption('no-overwrite');
        $container = $this->phpContainerName();
        $files     = (array) $input->getArgument('files');

        foreach ($files as $file) {
            if (!$this->files->existsInContainer($container, $file)) {
                $output->writeln(sprintf('Looks like "%s" doesn\'t exist', $file));
                return;
            }

            if ($overwrite && is_dir($file)) {
                //we only remove if the file exists and is a directory
                //as the new directory we push may have a different set of files in it
                //for files we can just overwrite and save some cycles
                $this->files->deleteLocally([$file]);
            }
        }

        $this->files->download($container, $files);
    }
}
