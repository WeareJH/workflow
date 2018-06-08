<?php declare(strict_types=1);

namespace Jh\Workflow\Config;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
interface ConfigGeneratorInterface
{
    /**
     * @param string $rootDir Root directory for Platform installation
     * @return void
     */
    public function generateEnvironmentConfig(string $rootDir);
}
