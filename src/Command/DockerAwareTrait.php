<?php

namespace Jh\Workflow\Command;

use M1\Env\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait DockerAwareTrait
{
    private function getDevEnvironmentVars(): array
    {
        $envFile = getcwd() . '/.docker/local.env';

        if (!file_exists($envFile)) {
            throw new \RuntimeException("Local env file doesn't exist, are you sure your configured correctly?");
        }

        return Parser::parse(file_get_contents($envFile));
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
            throw new \RuntimeException(sprintf("Unable to parse docker-compose file \n\n %s", $e->getMessage()));
        }

        $yaml = array_merge_recursive($coreYaml, $devYaml);

        if (!isset($yaml['services'][$service])) {
            throw new \RuntimeException(sprintf('Service "%s" doesn\'t exist in the compose files', $service));
        }

        return $yaml['services'][$service];
    }
}
