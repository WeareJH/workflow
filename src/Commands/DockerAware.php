<?php

namespace Jh\Workflow\Commands;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
trait DockerAware
{
    private function phpContainerName() : string
    {
        return $this->getContainerName('php');
    }

    private function nginxContainerName() : string
    {
        return $this->getContainerName('nginx');
    }

    private function getContainerName(string $service) : string
    {
        $compasePath = __DIR__ . '/../../../../../docker-compose.yml';

        try {
            $yaml = Yaml::parse(file_get_contents($compasePath));
        } catch (ParseException $e) {
            echo sprintf("Unable to parse docker-compose.yml \n\n %s", $e->getMessage());
        }

        if (!isset($yaml['services'][$service]['container_name'])) {
            echo sprintf("Unable to get container name for service %s", $service);
        }

        return $yaml['services'][$service]['container_name'];
    }
}
