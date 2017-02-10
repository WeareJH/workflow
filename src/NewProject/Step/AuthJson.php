<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Jh\Workflow\NewProject\TemplateWriter;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class AuthJson implements StepInterface
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
        $output->success(sprintf('Creating auth.json in %s with provided keys', $details->getProjectName()));

        $this->templateWriter->fillAndWriteTemplate(
            $details->getProjectName(),
            'composer/auth.json',
            'auth.json',
            [
                'pubkey' => $details->getPubKey(),
                'prikey' => $details->getPrivKey(),
            ]
        );
    }
}
