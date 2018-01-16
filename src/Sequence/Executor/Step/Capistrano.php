<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Jh\Workflow\Template;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Capistrano implements StepInterface
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
        $style->title('Adding Capistrano config');

        $path = $data->getPath();

        $cp = [
            'capistrano/Capfile' => 'Capfile',
            'capistrano/Gemfile' => 'Gemfile',
        ];
        foreach ($cp as $template => $file) {
            $this->template->cp($template, "{$path}/{$file}");
        }

        $rcp = [
            'capistrano/deploy.rb' => 'cap/deploy.rb',
            'capistrano/dev.rb'    => 'cap/deploy/dev.rb',
        ];
        $replace = [
            'project-name'      => $data->getProjectName(),
            'project-namespace' => $data->getProjectNamespace(),
            'repository'        => $data->getRepository(),
        ];
        foreach ($rcp as $template => $file) {
            $this->template->repcp($template, "{$path}/{$file}", $replace);
        }

        $style->success('Added Capistrano config');
    }
}
