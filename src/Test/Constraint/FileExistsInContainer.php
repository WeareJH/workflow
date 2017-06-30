<?php

namespace Jh\Workflow\Test\Constraint;


use PHPUnit\Framework\Constraint\Constraint;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileExistsInContainer extends Constraint
{

    /**
     * @var string
     */
    private $container;

    public function __construct(string $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    public function matches($other)
    {
        if (!is_string($other)) {
            return false;
        }

        try {
            $this->exec(sprintf('docker exec %s test -e %s', $this->container, $other));
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    private function exec(string $command)
    {
        exec($command, $output, $exitCode);

        if ($exitCode > 0) {
            throw new \RuntimeException('Command failed with exit code: ' . $exitCode);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'exists in container ' . $this->container;
    }
}
