<?php declare(strict_types=1);

namespace Jh\Workflow\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class M1ConfigGenerator implements ConfigGeneratorInterface
{
    public function generateEnvironmentConfig(string $rootDir, SymfonyStyle $output)
    {
        $outputPath = file_exists($rootDir . '/htdocs')
            ? $rootDir . '/htdocs/app/etc/local.xml'
            : $rootDir . '/app/etc/local.xml';

        if (!file_exists(dirname($outputPath)) && !mkdir(dirname($outputPath), 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create path "%s"', dirname($outputPath)));
        }

        $config = file_get_contents(__DIR__ . '/../../templates/config/M1/local.xml.template');
        file_put_contents($outputPath, $config);

        $output->success('Fresh configuration written to ' . $outputPath);
    }
}
