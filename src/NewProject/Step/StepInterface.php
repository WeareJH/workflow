<?php

namespace Jh\Workflow\NewProject\Step;

use Jh\Workflow\NewProject\Details;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
interface StepInterface
{
    public function run(Details $details, OutputStyle $output);
}
