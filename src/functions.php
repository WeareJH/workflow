<?php

use Jh\Workflow\ProcessFactory;

function toMap($function)
{
    return function ($item, $key) use ($function) {
        return $function($item);
    };
}
