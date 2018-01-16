<?php

namespace Jh\Workflow\Details;

/**
 * Workflow project details DTO
 *
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Data implements DataInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $visibleData = [];

    public function __construct(array $data = [])
    {
        $defaults = [
            'github_oauth_token' => '',
            'magento_edition' => self::MAGENTO_CE,
            'magento_private_key' => '',
            'magento_public_key' => '',
            'path' => '',
            'project_domain' => '',
            'project_name' => '',
            'project_namespace' => '',
            'repository' => '',
            'use_rabbitmq' => false,
        ];

        $data = array_intersect_key($data, $defaults);
        $this->data = array_merge($defaults, $data);
    }

    public function addVisibleData($label, $value) : DataInterface
    {
        $this->visibleData[] = [$label, $value];
        return $this;
    }

    public function getGitHubOAuthToken() : string
    {
        return $this->data['github_oauth_token'];
    }

    public function getMagentoEdition() : int
    {
        return $this->data['magento_edition'];
    }

    public function getMagentoPrivateKey() : string
    {
        return $this->data['magento_private_key'];
    }

    public function getMagentoPublicKey() : string
    {
        return $this->data['magento_public_key'];
    }

    public function getPath() : string
    {
        return $this->data['path'];
    }

    public function getProjectDomain() : string
    {
        if (1 > strlen($this->data['project_domain'])) {
            $name = strtolower($this->getProjectNamespace());
            $this->setProjectDomain("{$name}.dev");
        }
        return $this->data['project_domain'];
    }

    public function getProjectName() : string
    {
        return $this->data['project_name'];
    }

    public function getProjectNamespace() : string
    {
        return $this->data['project_namespace'];
    }

    public function getRepository() : string
    {
        return $this->data['repository'];
    }

    public function getUseRabbitMQ() : bool
    {
        return $this->data['use_rabbitmq'];
    }

    public function getVisibleData() : array
    {
        return $this->visibleData;
    }

    public function setGitHubOAuthToken($token) : DataInterface
    {
        $this->data['github_oauth_token'] = $token;
        return $this;
    }

    public function setMagentoEdition(int $edition) : DataInterface
    {
        $this->data['magento_edition'] = $edition;
        return $this;
    }

    public function setMagentoPrivateKey($key) : DataInterface
    {
        $this->data['magento_private_key'] = $key;
        return $this;
    }

    public function setMagentoPublicKey($key) : DataInterface
    {
        $this->data['magento_public_key'] = $key;
        return $this;
    }

    public function setPath($path) : DataInterface
    {
        $this->data['path'] = $path;
        return $this;
    }

    public function setProjectDomain($domain) : DataInterface
    {
        $this->data['project_domain'] = $domain;
        return $this;
    }

    public function setProjectName($name) : DataInterface
    {
        $this->data['project_name'] = $name;
        return $this;
    }

    public function setProjectNamespace($ns) : DataInterface
    {
        $this->data['project_namespace'] = $ns;
        return $this;
    }

    public function setRepository($repo) : DataInterface
    {
        $this->data['repository'] = $repo;
        return $this;
    }

    public function setUseRabbitMQ(bool $flag) : DataInterface
    {
        $this->data['use_rabbitmq'] = $flag;
        return $this;
    }
}
