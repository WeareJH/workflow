<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\CommandLine;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class GitInit implements StepInterface
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
        $style->title("Initialising git repository");

        $path = $data->getPath();
        $repo = $data->getRepository();

        $command = <<<SHELL
cd {$path} \
&& git init {$path} \
&& git remote add origin {$repo} \
&& git add . \
&& git commit -m "Add Magento"
SHELL;

        $this->cl->run($command);
        $style->success('Initialised repository');
    }
}
