<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class ProvisionCodeDir implements StepInterface
{
    /**
     * @var Template
     */
    private $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $style->title("Provisioning application code directory");

        $path = $data->getPath();
        $ns   = $data->getProjectNamespace();

        $rel = "app/code/{$ns}";
        $abs = "{$path}/{$rel}";

        $this->template->touch("{$abs}/.gitkeep");
        $style->success("Provisioned application code directory in {$rel}");
    }
}
