<?php

use Interop\Container\ContainerInterface;
use Jh\Workflow\Application;
use Jh\Workflow\Command;
use Jh\Workflow\CommandLine;
use Jh\Workflow\Details;
use Jh\Workflow\Details\Collector\Step as CollectorStep;
use Jh\Workflow\Files;
use Jh\Workflow\FitProject;
use Jh\Workflow\NewProject;
use Jh\Workflow\NullLogger;
use Jh\Workflow\Sequence\Executor\Step as ExecutorStep;
use Jh\Workflow\WatchFactory;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use Rx\Scheduler;
use Rx\Scheduler\EventLoopScheduler;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

return [
    Application::class => function (ContainerInterface $c) {
        $app = new Application('JH Workflow Tool');
        $app->getDefinition()->addOption(new InputOption('--debug', null, InputOption::VALUE_NONE, 'Debug Mode'));

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
        $app->add($c->get(Command\FitCommand::class));
        $app->add($c->get(Command\NewCommand::class));
        $app->add($c->get(Command\Php::class));
        $app->add($c->get(Command\Exec::class));
        $app->add($c->get(Command\Delete::class));
        $app->add($c->get(Command\DatabaseDump::class));

        $eventLoop = $c->get(LoopInterface::class);

        Scheduler::setDefaultFactory(function() use ($eventLoop) {
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
    ProcessBuilder::class  => DI\object(),
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
    WatchFactory::class => function (ContainerInterface $c) {
        return new WatchFactory($c->get(LoopInterface::class));
    },
    Files::class => function (ContainerInterface $c) {
        return new Files($c->get(CommandLine::class), $c->get(OutputInterface::class));
    },

    \Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
        return new \Jh\Workflow\Logger($c->get(OutputInterface::class));
    },

    // Commands
    Command\Watch::class              => function (ContainerInterface $c) {
        return new Command\Watch($c->get(WatchFactory::class), $c->get(Files::class));
    },

    // // Fit project steps
    // StepRunnerFit::class => function (ContainerInterface $c) {
    //     return new StepRunnerFit([
    //         $c->get(FitStep\Clean::class),
    //         // $c->get(Step\AuthJson::class),
    //         // $c->get(Step\ComposerJson::class),
    //         // $c->get(Step\Docker::class),
    //         // $c->get(Step\PrTemplate::class),
    //         // $c->get(Step\Readme::class),
    //         // $c->get(Step\CircleCI::class),
    //         // $c->get(Step\Capistrano::class),
    //         // $c->get(Step\PhpStorm::class),
    //         // $c->get(Step\GitCommit::class),
    //     ]);
    // },

    ExecutorStep\Clean::class => function (ContainerInterface $c) {
        return new ExecutorStep\Clean([
            '.htaccess',
            '.htaccess.sample',
            '.php_cs',
            '.travis.yml',
            'CHANGELOG.md',
            'COPYING.txt',
            'CONTRIBUTING.md',
            'Gruntfile.js.sample',
            'ISSUE_TEMPLATE.md',
            'LICENSE.txt',
            'LICENSE_AFL.txt',
            'LICENSE_EE.txt',
            'nginx.conf.sample',
            'package.json.sample',
            'php.ini.sample',
            'README_EE.txt',
        ]);
    },

    FitProject\Details\Collector::class => function (ContainerInterface $c) {
        return new FitProject\Details\Collector([
            $c->get(CollectorStep\Path::class),
            $c->get(CollectorStep\Repository::class),
            $c->get(CollectorStep\Auth::class),
            $c->get(CollectorStep\MagentoEdition::class),
            $c->get(CollectorStep\UseRabbitMQ::class),
            $c->get(CollectorStep\ProjectName::class),
            $c->get(CollectorStep\ProjectNamespace::class),
            $c->get(CollectorStep\ProjectDomain::class)
        ]);
    },

    FitProject\Sequence\Executor::class => function (ContainerInterface $c) {
        return new FitProject\Sequence\Executor([
            $c->get(ExecutorStep\Gitignore::class),
            $c->get(ExecutorStep\Clean::class),
            $c->get(ExecutorStep\ProvisionCodeDir::class),
            $c->get(ExecutorStep\AuthJson::class),
            $c->get(ExecutorStep\Docker::class),
            $c->get(ExecutorStep\CircleCI::class),
            $c->get(ExecutorStep\Capistrano::class),
            $c->get(ExecutorStep\PhpStorm::class),
            $c->get(ExecutorStep\PRTemplate::class),
            $c->get(ExecutorStep\Readme::class),
            $c->get(ExecutorStep\ComposerJson::class),
            $c->get(ExecutorStep\GitCommit::class),
        ]);
    },

    NewProject\Details\Collector::class => function (ContainerInterface $c) {
        return new NewProject\Details\Collector([
            $c->get(CollectorStep\Path::class),
            $c->get(CollectorStep\Repository::class),
            $c->get(CollectorStep\Auth::class),
            $c->get(CollectorStep\MagentoEdition::class),
            $c->get(CollectorStep\UseRabbitMQ::class),
            $c->get(CollectorStep\ProjectName::class),
            $c->get(CollectorStep\ProjectNamespace::class),
            $c->get(CollectorStep\ProjectDomain::class)
        ]);
    },

    NewProject\Sequence\Executor::class => function (ContainerInterface $c) {
        return new NewProject\Sequence\Executor([
            $c->get(ExecutorStep\ComposerCreateProject::class),
            $c->get(ExecutorStep\Gitignore::class),
            $c->get(ExecutorStep\GitInit::class),
            $c->get(ExecutorStep\Clean::class),
            $c->get(ExecutorStep\ProvisionCodeDir::class),
            $c->get(ExecutorStep\AuthJson::class),
            $c->get(ExecutorStep\Docker::class),
            $c->get(ExecutorStep\CircleCI::class),
            $c->get(ExecutorStep\Capistrano::class),
            $c->get(ExecutorStep\PhpStorm::class),
            $c->get(ExecutorStep\PRTemplate::class),
            $c->get(ExecutorStep\Readme::class),
            $c->get(ExecutorStep\ComposerJson::class),
            $c->get(ExecutorStep\GitCommit::class),
        ]);
    },
];
