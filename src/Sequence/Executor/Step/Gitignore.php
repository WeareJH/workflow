<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Gitignore implements StepInterface
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
        $style->title('Adding gitignore');

        $path = $data->getPath();
        $this->template->cp('.gitignore', "{$path}/.gitignore");

        $style->success('Added gitignore');
    }
}
