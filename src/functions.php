<?php

function toMap($function)
{
    return function ($item, $key) use ($function) {
        return $function($item);
    };
}