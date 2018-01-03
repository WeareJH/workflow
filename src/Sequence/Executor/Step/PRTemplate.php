<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

class PRTemplate implements StepInterface
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
        $style->title('Adding PR template');

        $path = $data->getPath();
        $this->template->cp('PULL_REQUEST_TEMPLATE.md', "{$path}/PULL_REQUEST_TEMPLATE.md");

        $style->success('Added PR template');
    }
}
