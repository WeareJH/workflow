<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class PrTemplate implements StepInterface
{
    /**
     * @var TemplateWriter
     */
    private $templateWriter;

    public function __construct(TemplateWriter $templateWriter)
    {
        $this->templateWriter = $templateWriter;
    }

    public function run(Details $details, OutputStyle $output)
    {
        $output->success('Adding PR template');

        $this->templateWriter->copyTemplate(
            $details->getProjectName(),
            'pr/PULL_REQUEST_TEMPLATE.md',
            'PULL_REQUEST_TEMPLATE.md'
        );
    }
}
