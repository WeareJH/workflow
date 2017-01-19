<?php

/**
 * Interface CommandInterface
 */
interface CommandInterface
{
    public function invoke(array $containers, array $arguments);
}
