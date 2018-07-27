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

        if (!file_exists($coreComposePath)) {
            throw new \RuntimeException('Could not locate docker-compose.yml file. Are you in the right directory?');
        }

        try {
            $coreYaml  = Yaml::parse(file_get_contents($coreComposePath));
            $devYaml   = file_exists($devComposePath) ? Yaml::parse(file_get_contents($devComposePath)) : [];
        } catch (ParseException $e) {
            throw new \RuntimeException(sprintf("Unable to parse docker-compose file \n\n %s", $e->getMessage()));
        }

        $yaml = array_merge_recursive($coreYaml, $devYaml);

        if (!isset($yaml['services'][$service])) {
            throw new \RuntimeException(sprintf('Service "%s" doesn\'t exist in the compose files', $service));
        }

        return $yaml['services'][$service];
    }

    private function getComposeFileFlags(bool $prod = false)
    {
        $cwd = getcwd();

        $devComposePath   = $cwd . '/docker-compose.dev.yml';
        $prodComposePath  = $cwd . '/docker-compose.prod.yml';

        $composeFileFlags = '-f docker-compose.yml';

        if (!$prod && file_exists($devComposePath)) {
            $composeFileFlags .= ' -f docker-compose.dev.yml';
        }

        if ($prod && file_exists($prodComposePath)) {
            $composeFileFlags .= ' -f docker-compose.prod.yml';
        }

        return $composeFileFlags;
    }
}
