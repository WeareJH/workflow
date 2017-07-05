<?php

use Jh\Workflow\Application;
use Symfony\Component\Console\Output\OutputInterface;

switch (true) {
    case (file_exists(__DIR__ . '/../vendor/autoload.php')):
        // Installed standalone
        require __DIR__ . '/../vendor/autoload.php';
        break;
    case (file_exists(__DIR__ . '/../../../autoload.php')):
        // Installed as a Composer dependency
        require __DIR__ . '/../../../autoload.php';
        break;
    case (file_exists('vendor/autoload.php')):
        // As a Composer dependency, relative to CWD
        require 'vendor/autoload.php';
        break;
    default:
        throw new RuntimeException('Unable to locate Composer autoloader; please run "composer install".');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$container = (new \DI\ContainerBuilder())
    ->addDefinitions(__DIR__ . '/config.php')
    ->build();

exit($container->get(Application::class)->run(null, $container->get(OutputInterface::class)));
