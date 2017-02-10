<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Capistrano implements StepInterface
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
        $output->success('Adding capistrano config');

        $this->templateWriter->copyTemplate($details->getProjectName(), 'capistrano/Capfile', 'Capfile');
        $this->templateWriter->copyTemplate($details->getProjectName(), 'capistrano/Gemfile', 'Gemfile');

        $this->templateWriter->fillAndWriteTemplate(
            $details->getProjectName(),
            'capistrano/deploy.rb',
            'cap/deploy.rb',
            [
                'project-name' => $details->getProjectName()
            ]
        );

        $this->templateWriter->fillAndWriteTemplate(
            $details->getProjectName(),
            'capistrano/dev.rb',
            'cap/deploy/dev.rb',
            [
                'project-name' => $details->getProjectName()
            ]
        );
    }
}
