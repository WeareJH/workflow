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
    private $rabbitMQ;

    public function __construct(
        string $repo,
        string $projectName,
        string $namespace,
        string $version,
        string $pubKey,
        string $privKey,
        bool $rabbitMQ
    ) {
        $this->repo        = $repo;
        $this->projectName = $projectName;
        $this->namespace   = $namespace;
        $this->version     = $version;
        $this->pubKey      = $pubKey;
        $this->privKey     = $privKey;
        $this->rabbitMQ    = $rabbitMQ;
    }

    /**
     * @return mixed
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * @return mixed
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getPubKey()
    {
        return $this->pubKey;
    }

    /**
     * @return mixed
     */
    public function getPrivKey()
    {
        return $this->privKey;
    }

    public function getProjectDomain()
    {
        return strtolower($this->projectName) . '.dev';
    }

    public function includeRabbitMQ()
    {
        return $this->rabbitMQ;
    }
}
