<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\Command\ProcessRunnerTrait;
use Jh\Workflow\NewProject\Details;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GitCommit implements StepInterface
{
    use ProcessRunnerTrait;

    public function __construct(ProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    public function run(Details $details, OutputStyle $output)
    {
        $output->success('Commit config and pushing to repo');

        $cwd = getcwd();
        chdir($details->getProjectName());

        $command =  'git add .';
        $command .= ' && git commit -m "Project Config"';
        $command .= ' && git push origin master';

        $this->runProcessNoOutput($command);

        chdir($cwd);
    }
}
