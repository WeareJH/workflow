<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Readme implements StepInterface
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
        $style->title('Adding README');

        $replace = [
            'project-domain'    => $data->getProjectDomain(),
            'project-name'      => $data->getProjectName(),
            'project-namespace' => $data->getProjectNamespace(),
            'repository'        => $data->getRepository(),
            'workdir'           => strtolower($data->getProjectNamespace())
        ];

        $path = $data->getPath();
        $this->template->repcp('README.md', "{$path}/README.md", $replace);

        $style->success('Added README');
    }
}
