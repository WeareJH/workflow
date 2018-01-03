<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\CommandLine;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class GitCommit implements StepInterface
{
    /**
     * @var CommandLine
     */
    private $cl;

    public function __construct(CommandLine $cl)
    {
        $this->cl = $cl;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $style->title('Committing project configuration');
        $path = $data->getPath();

        $command = <<<SHELL
cd {$path} \
&& git add . \
&& git commit -m "Configured project with workflow"
SHELL;

        // && git push origin master?
        $this->cl->run($command);
        $style->success('Committed project configuration');
    }
}
