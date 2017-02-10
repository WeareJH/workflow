<?php

use Interop\Container\ContainerInterface;
use Jh\Workflow\Application;
use Jh\Workflow\Command;
use Jh\Workflow\ProcessFactory;
use Symfony\Component\Process\ProcessBuilder;

return [
    Application::class => function (ContainerInterface $c) {
        $app = new Application('JH Workflow Tool');

        $app->add($c->get(Command\Start::class));
        $app->add($c->get(Command\Stop::class));
        $app->add($c->get(Command\Restart::class));
        $app->add($c->get(Command\Build::class));
        $app->add($c->get(Command\Up::class));
        $app->add($c->get(Command\Magento::class));
        $app->add($c->get(Command\MagentoFullInstall::class));
        $app->add($c->get(Command\MagentoInstall::class));
        $app->add($c->get(Command\MagentoConfigure::class));
        $app->add($c->get(Command\Pull::class));
        $app->add($c->get(Command\Push::class));
        $app->add($c->get(Command\Watch::class));
        $app->add($c->get(Command\Sync::class));
        $app->add($c->get(Command\ComposerUpdate::class));
        $app->add($c->get(Command\ComposerRequire::class));
        $app->add($c->get(Command\Sql::class));
        $app->add($c->get(Command\Ssh::class));
        $app->add($c->get(Command\NginxReload::class));
        $app->add($c->get(Command\XdebugLoopback::class));

        return $app;
    },
    ProcessBuilder::class             => DI\object(),
    ProcessFactory::class             => DI\object(),
    Command\Build::class              => DI\object(),
    Command\Magento::class            => DI\object(),
    Command\MagentoFullInstall::class => DI\object(),
    Command\MagentoInstall::class     => DI\object(),
    Command\MagentoConfigure::class   => DI\object(),
    Command\Pull::class               => DI\object(),
    Command\Push::class               => DI\object(),
    Command\Start::class              => DI\object(),
    Command\Stop::class               => DI\object(),
    Command\Up::class                 => DI\object(),
    Command\Watch::class              => DI\object(),
    Command\Sync::class               => DI\object(),
    Command\ComposerUpdate::class     => DI\object(),
    Command\Sql::class                => DI\object(),
    Command\NginxReload::class        => DI\object(),
    Command\XdebugLoopback::class     => DI\object(),
    Command\Ssh::class     => DI\object(),
];
