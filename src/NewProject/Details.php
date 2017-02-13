<?php

namespace Jh\Workflow\NewProject;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Details
{
    private $repo;
    private $projectName;
    private $namespace;
    private $version;
    private $pubKey;
    private $privKey;
    private $accessToken;
    private $rabbitMQ;

    public function __construct(
        string $repo,
        string $projectName,
        string $namespace,
        string $version,
        string $pubKey,
        string $privKey,
        string $accessToken,
        bool $rabbitMQ
    ) {
        $this->repo        = $repo;
        $this->projectName = $projectName;
        $this->namespace   = $namespace;
        $this->version     = $version;
        $this->pubKey      = $pubKey;
        $this->privKey     = $privKey;
        $this->accessToken = $accessToken;
        $this->rabbitMQ    = $rabbitMQ;
    }

    public function getRepo() : string
    {
        return $this->repo;
    }

    public function getProjectName() : string
    {
        return $this->projectName;
    }

    public function getNamespace() : string
    {
        return $this->namespace;
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getPubKey() : string
    {
        return $this->pubKey;
    }

    public function getPrivKey() : string
    {
        return $this->privKey;
    }

    public function getProjectDomain() : string
    {
        return strtolower($this->projectName) . '.dev';
    }

    public function getAccessToken() : string
    {
        return $this->accessToken;
    }

    public function includeRabbitMQ() : bool
    {
        return $this->rabbitMQ;
    }
}
