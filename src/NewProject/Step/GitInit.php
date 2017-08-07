<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GitInit implements StepInterface
{
    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        $this->commandLine = $commandLine;
    }

    public function run(Details $details, OutputStyle $output)
    {
        $output->success('Initialising git repo');

        $cwd = getcwd();
        chdir($details->getProjectName());

        $command =  'git init';
        $command .= ' && git remote add origin ' . $details->getRepo();
        $command .= ' && git add .';
        $command .= ' && git commit -m "Add magento"';

        $this->commandLine->run($command);

        chdir($cwd);

        $output->success('Initialised repo');
    }
}
