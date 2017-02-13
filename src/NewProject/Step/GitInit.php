<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GitInit implements StepInterface
{
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
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

        $this->runProcessNoOutput($command);

        chdir($cwd);

        $output->success('Initialised repo');
    }
}
