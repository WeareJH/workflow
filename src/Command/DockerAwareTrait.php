<?php

namespace Jh\Workflow\Command;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait DockerAwareTrait
{
    // TODO: Clean up, use lib to parse env file, throw exceptions
    private function getDevEnvironmentVars(): array
    {
        $envFile = getcwd() . '/.docker/local.env';

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

    private function getContainerName(string $service): string
    {
        $serviceConfig = $this->getServiceConfig($service);

        if (!isset($serviceConfig['container_name'])) {
            throw new \RuntimeException(sprintf('Unable to get container name for service %s', $service));
        }

        return $serviceConfig['container_name'];
    }

    private function getServiceConfig(string $service) : array
    {
        $cwd = getcwd();

        $coreComposePath  = $cwd . '/docker-compose.yml';
        $devComposePath   = $cwd . '/docker-compose.dev.yml';

        try {
            $coreYaml  = Yaml::parse(file_get_contents($coreComposePath));
            $devYaml   = Yaml::parse(file_get_contents($devComposePath));
        } catch (ParseException $e) {
            echo sprintf("Unable to parse docker-compose file \n\n %s", $e->getMessage());
            return;
        }

        $yaml = array_merge_recursive($coreYaml, $devYaml);

        if (!isset($yaml['services'][$service])) {
            throw new \RuntimeException(sprintf('Service "%s" doesn\'t exist in the compose files', $service));
        }

        return $yaml['services'][$service];
    }
}
