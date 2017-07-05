<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Files;
use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Jh\Workflow\ProcessFactory;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Delete extends Command implements CommandInterface
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
            ->setName('delete')
            ->setDescription('Delete files from the container')
            ->addArgument(
                'files',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Files to delete, relative to project root'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->files->delete($this->phpContainerName(), (array) $input->getArgument('files'));
    }
}
