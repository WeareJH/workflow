<?php

use Jh\Workflow\ProcessFactory;

function toMap($function)
{
    return function ($item, $key) use ($function) {
        return $function($item);
    };
}

function runProcessShowingOutput($command, $workingDirectory = null) : string
{
    global $c;
    $output = $c->get(\Symfony\Component\Console\Output\OutputInterface::class);

    return $c->get(ProcessFactory::class)->runSynchronous($command, $workingDirectory, function ($type, $buffer) use ($output) {
        $output->writeLine($buffer);
    });
}