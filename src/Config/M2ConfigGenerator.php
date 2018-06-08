<?php declare(strict_types=1);

namespace Jh\Workflow\Config;

use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class M2ConfigGenerator implements ConfigGeneratorInterface
{
    /**
     * @var SymfonyStyle
     */
    private $output;

    public function __construct(SymfonyStyle $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $rootDir
     * @throws \RuntimeException When the
     */
    public function generateEnvironmentConfig(string $rootDir)
    {
        $outputPath = $rootDir . '/app/etc/env.php';

        if (!file_exists(dirname($outputPath)) && !mkdir(dirname($outputPath), 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create path "%s"', dirname($outputPath)));
        }

        $standardConfig = file_get_contents(__DIR__ . '/../../templates/config/M2/env.php.template');

        $mode   = $this->output->choice('Which Magento deployment mode ?', ['developer', 'production'], 'developer');
        $queues = $this->output->confirm('Do you require queue (e.g. RabbitMQ) configuration ?', false);

        $config = str_replace(
            ['{mage-mode}', '{use-rabbit}'],
            [$mode, $queues ? '' : '#'],
            $standardConfig
        );

        file_put_contents($outputPath, $config);

        $this->output->success('Fresh configuration written to ' . $outputPath);
    }
}
