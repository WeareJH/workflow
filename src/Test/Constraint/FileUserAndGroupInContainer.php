<?php

namespace Jh\Workflow\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileUserAndGroupInContainer extends Constraint
{
    /**
     * @var string
     */
    private $container;

    /**
     * @var string
     */
    private $group;
    /**
     * @var string
     */
    private $user;

    public function __construct(string $container, string $group, string $user)
    {
        parent::__construct();
        $this->container = $container;
        $this->group = $group;
        $this->user = $user;
    }

    public function matches($other)
    {
        if (!is_string($other)) {
            return false;
        }

        try {
            return sprintf('%s:%s', $this->group, $this->user) ===
                $this->exec(sprintf('docker exec %s stat -c "%%G:%%U" %s', $this->container, $other));
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    private function exec(string $command) : string
    {
        exec($command, $output, $exitCode);

        if ($exitCode > 0) {
            throw new \RuntimeException('Command failed with exit code: ' . $exitCode);
        }
        
        return trim(implode("\n", $output));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'has correct group and user in container ' . $this->container;
    }
}
