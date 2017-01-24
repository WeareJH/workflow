<?php

namespace Jh\Workflow\Commands;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait DockerAware
{
    private function getDevEnvironmentVars(): array
    {
        $envFile = __DIR__ . '/../../../../../.docker/local.env';

        if (!file_exists($envFile)) {
            echo "Local env file doesn't exist, are you sure your configured correctly?";
            return;
        }

        $lines = file($envFile);

        $values = array_filter(array_map(function ($line) {
            return explode('=', trim($line));
        }, $lines), function ($line) {
            return count($line) === 2;
        });

        return array_column($values, 1, 0);
    }

    private function phpContainerName(): string
    {
        return $this->getContainerName('php');
    }

    private function nginxContainerName(): string
    {
        return $this->getContainerName('nginx');
    }

    private function getContainerName(string $service): string
    {
        $coreComposePath  = __DIR__ . '/../../../../../docker-compose.yml';
        $devComposePath   = __DIR__ . '/../../../../../docker-compose.dev.yml';

        try {
            $coreYaml  = Yaml::parse(file_get_contents($coreComposePath));
            $devYaml   = Yaml::parse(file_get_contents($devComposePath));
        } catch (ParseException $e) {
            echo sprintf("Unable to parse docker-compose file \n\n %s", $e->getMessage());
            return;
        }

        $yaml = array_merge_recursive($coreYaml, $devYaml);

        if (!isset($yaml['services'][$service]['container_name'])) {
            echo sprintf("Unable to get container name for service %s", $service);
        }

        return $yaml['services'][$service]['container_name'];
    }
}
