<?php declare(strict_types=1);

namespace Jh\Workflow\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
interface ConfigGeneratorInterface
{
    /**
     * @param string $rootDir Root directory for Platform installation
     * @param SymfonyStyle $output
     * @return void
     */
    public function generateEnvironmentConfig(string $rootDir, SymfonyStyle $output);
}
