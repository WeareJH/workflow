<?php

use Interop\Container\ContainerInterface;
use Jh\Workflow\Application;
use Jh\Workflow\Command;
use Jh\Workflow\CommandLine;
use Jh\Workflow\NewProject\Step;
use Jh\Workflow\NewProject\StepRunner;
use Jh\Workflow\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use Rx\Scheduler;
use Rx\Scheduler\EventLoopScheduler;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

return [
    Application::class => function (ContainerInterface $c) {
        $app = new Application('JH Workflow Tool');
        $app->getDefinition()->addOption(new InputOption('--debug', null, InputOption::VALUE_NONE, 'Debug Mode'));

        $app->add($c->get(Command\Stop::class));
        $app->add($c->get(Command\Down::class));
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
        $app->add($c->get(Command\MagentoSetupUpgrade::class));
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
        $app->add($c->get(Command\NewProject::class));
        $app->add($c->get(Command\Php::class));
        $app->add($c->get(Command\Exec::class));
        $app->add($c->get(Command\Delete::class));
        $app->add($c->get(Command\DatabaseDump::class));
        $app->add($c->get(Command\GenerateConfig::class));
        $app->add($c->get(Command\VarnishEnable::class));
        $app->add($c->get(Command\VarnishDisable::class));
        $app->add($c->get(Command\GenerateConfig::class));

        $eventLoop = $c->get(LoopInterface::class);

        Scheduler::setDefaultFactory(function () use ($eventLoop) {
            return new EventLoopScheduler($eventLoop);
        });

        register_shutdown_function(function () use ($eventLoop) {
             $eventLoop->run();
        });

        return $app;
    },
    InputInterface::class => function () {
        return new ArgvInput();
    },
    OutputInterface::class => function () {
        return new ConsoleOutput;
    },
    LoopInterface::class => function () {
        return new StreamSelectLoop;
    },
    CommandLine::class  => function (ContainerInterface $c) {
        if (in_array('--debug', $GLOBALS['argv'], true)) {
            $logger = new \Jh\Workflow\Logger($c->get(OutputInterface::class));
        } else {
            $logger = new NullLogger;
        }

        return new CommandLine($c->get(LoopInterface::class), $logger, $c->get(OutputInterface::class));
    },
    \Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
        return new \Jh\Workflow\Logger($c->get(OutputInterface::class));
    },

    // New Project Steps
    StepRunner::class => function (ContainerInterface $c) {
        return new StepRunner($c->get('steps'));
    },

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
            $c->get(Step\GitClean::class),
        ];
    }
];
