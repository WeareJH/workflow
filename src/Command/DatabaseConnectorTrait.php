<?php

namespace Jh\Workflow\Command;

trait DatabaseConnectorTrait
{
    use DockerAwareTrait;

    private function getDbDetails() : array
    {
        $envVars   = $this->getDevEnvironmentVars();
        return [
            'user' => 'root',
            'pass' => $envVars['MYSQL_ROOT_PASSWORD'] ?? 'docker',
            'db'   => $envVars['MYSQL_DATABASE'] ?? 'docker'
        ];
    }
}
