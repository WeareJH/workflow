<?php

namespace Jh\Workflow\Details;

interface DataInterface
{
    const MAGENTO_CE = 0;
    const MAGENTO_EE = 1;

    public function addVisibleData($label, $value) : DataInterface;

    public function getGitHubOAuthToken()  : string;
    public function getMagentoEdition()    : int;
    public function getMagentoPrivateKey() : string;
    public function getMagentoPublicKey()  : string;
    public function getPath()              : string;
    public function getProjectDomain()     : string;
    public function getProjectName()       : string;
    public function getProjectNamespace()  : string;
    public function getRepository()        : string;
    public function getUseRabbitMQ()       : bool;
    public function getVisibleData()       : array;

    public function setGitHubOAuthToken($token)     : DataInterface;
    public function setMagentoEdition(int $edition) : DataInterface;
    public function setMagentoPrivateKey($key)      : DataInterface;
    public function setMagentoPublicKey($key)       : DataInterface;
    public function setPath($path)                  : DataInterface;
    public function setProjectDomain($domain)       : DataInterface;
    public function setProjectName($name)           : DataInterface;
    public function setProjectNamespace($ns)        : DataInterface;
    public function setRepository($repo)            : DataInterface;
    public function setUseRabbitMQ(bool $flag)      : DataInterface;
}
