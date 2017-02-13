<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Readme implements StepInterface
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
        $output->success('Adding Readme');

        $this->templateWriter->fillAndWriteTemplate(
            $details->getProjectName(),
            'readme/README.md',
            'README.md',
            [
                'project-name' => $details->getProjectName(),
                'project-namespace' => $details->getNamespace(),
                'project-domain' => $details->getProjectDomain(),
                'project-repo' => $details->getRepo()
            ]
        );
    }
}
