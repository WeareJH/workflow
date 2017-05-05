<?php

use Interop\Container\ContainerInterface;
use Jh\Workflow\Application;
use Jh\Workflow\Command;
use Jh\Workflow\NewProject\DetailsGatherer;
use Jh\Workflow\NewProject\Step;
use Jh\Workflow\NewProject\StepRunner;
use Jh\Workflow\NewProject\TemplateWriter;
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
        $app->add($c->get(Command\MagentoCompile::class));
        $app->add($c->get(Command\MagentoModuleEnable::class));
        $app->add($c->get(Command\MagentoModuleDisable::class));
        $app->add($c->get(Command\Pull::class));
        $app->add($c->get(Command\Push::class));
        $app->add($c->get(Command\Watch::class));
        $app->add($c->get(Command\Sync::class));
        $app->add($c->get(Command\ComposerUpdate::class));
        $app->add($c->get(Command\ComposerInstall::class));
        $app->add($c->get(Command\ComposerRequire::class));
        $app->add($c->get(Command\Sql::class));
        $app->add($c->get(Command\Ssh::class));
        $app->add($c->get(Command\NginxReload::class));
        $app->add($c->get(Command\XdebugLoopback::class));
        $app->add($c->get(Command\NewProject::class));
        $app->add($c->get(Command\Php::class));
        $app->add($c->get(Command\Exec::class));

        return $app;
    },
    ProcessBuilder::class  => DI\object(),
    ProcessFactory::class  => DI\object(),
    TemplateWriter::class  => DI\object(),
    DetailsGatherer::class => DI\object(),

    // Commands
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
    Command\Ssh::class                => DI\object(),
    Command\NewProject::class         => DI\object(),
    Command\Php::class                => DI\object(),
    Command\Exec::class                => DI\object(),

    // New Project Steps
    StepRunner::class => function (ContainerInterface $c) {
        return new StepRunner($c->get('steps'));
    },
    Step\CreateProject::class => DI\object(),
    Step\GitInit::class       => DI\object(),
    Step\AuthJson::class      => DI\object(),
    Step\ComposerJson::class  => DI\object(),
    Step\Docker::class        => DI\object(),
    Step\PrTemplate::class    => DI\object(),
    Step\Readme::class        => DI\object(),
    Step\CircleCI::class      => DI\object(),
    Step\Capistrano::class    => DI\object(),
    Step\PhpStorm::class      => DI\object(),
    Step\GitCommit::class     => DI\object(),

    'steps' => function (ContainerInterface $c) {
        return [
            $c->get(Step\CreateProject::class),
            $c->get(Step\GitInit::class),
            $c->get(Step\AuthJson::class),
            $c->get(Step\ComposerJson::class),
            $c->get(Step\Docker::class),
            $c->get(Step\PrTemplate::class),
            $c->get(Step\Readme::class),
            $c->get(Step\CircleCI::class),
            $c->get(Step\Capistrano::class),
            $c->get(Step\PhpStorm::class),
            $c->get(Step\GitCommit::class),
        ];
    }
];
