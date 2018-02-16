<?php

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\ComposerUpdate;
use Jh\Workflow\Command\Pull;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Diego Cabrejas <diego@wearejh.com>
 */
class ComposerUpdateTest extends AbstractTestCommand
{
    /**
     * @var ComposerUpdate
     */
    private $command;

    /**
     * @var ObjectProphecy|Application
     */
    private $application;

    /**
     * @var ObjectProphecy|Pull
     */
    private $pullCommand;


    public function setUp()
    {
        parent::setUp();

        $this->command     = new ComposerUpdate($this->commandLine->reveal());
        $this->application = $this->prophesize(Application::class);
        $this->pullCommand = $this->prophesize(Pull::class);

        $this->application->getHelperSet()->willReturn(new HelperSet);
        $this->application->find('pull')->willReturn($this->pullCommand->reveal());

        $this->command->setApplication($this->application->reveal());
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Runs composer update inside the container and pulls back required files to the host';

        static::assertEquals('composer-update', $this->command->getName());
        static::assertEquals(['cu'], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testComposerUpdateCommand()
    {
        $this->useValidEnvironment();

        $this->output->getVerbosity()->willReturn(OutputInterface::OUTPUT_NORMAL);

        $cmd = 'docker exec -u www-data m2-php date +"%Y-%m-%d %H:%M"';
        $this->commandLine->runQuietly($cmd)->willReturn('2018-02-01 16:00');
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $cmd = 'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache m2-php composer update -o --ansi';
        $this->commandLine->run($cmd)->shouldBeCalled();

        $this->mockFindModifiedFilesMethods();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    /**
     * @param $verbosity
     * @param $expectedFlag
     * @dataProvider composerUpdateVerbosityProvider
     */
    public function testComposerUpdatePassesVerbosityCorrectly($verbosity, $expectedFlag)
    {
        $this->useValidEnvironment();

        $this->output->getVerbosity()->willReturn($verbosity);

        $cmd = 'docker exec -u www-data m2-php date +"%Y-%m-%d %H:%M"';
        $this->commandLine->runQuietly($cmd)->willReturn('2018-02-01 16:00');
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $cmd = sprintf(
            'docker exec -u www-data -e COMPOSER_CACHE_DIR=.docker/composer-cache m2-php composer update -o --ansi %s',
            $expectedFlag
        );
        $this->commandLine->run($cmd)->shouldBeCalled();

        $this->mockFindModifiedFilesMethods();

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function composerUpdateVerbosityProvider()
    {
        return [
            [OutputInterface::VERBOSITY_VERBOSE, '-v'],
            [OutputInterface::VERBOSITY_VERY_VERBOSE, '-vv'],
            [OutputInterface::VERBOSITY_DEBUG, '-vvv'],
        ];
    }

    public function testExceptionThrownIfContainerNameNotFound()
    {
        $this->useInvalidEnvironment();
        $this->expectException(\RuntimeException::class);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    private function mockFindModifiedFilesMethods() {

        $this->commandLine->run("docker exec -u www-data m2-php test -e 'vendor'")->shouldBeCalled();

        $cmd = "docker exec -u www-data m2-php find vendor/. -maxdepth 1 -newermt '2018-02-01 16:00' -exec basename {}, \\;";
        $this->commandLine->runQuietly($cmd)->willReturn(".,\nmagento,\nsymfony");
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $this->commandLine->run("docker exec -u www-data m2-php test -e '.docker/composer-cache/files'")->shouldBeCalled();
        $cmd = "docker exec -u www-data m2-php find .docker/composer-cache/files/. -maxdepth 1 -newermt '2018-02-01 16:00' -exec basename {}, \\;";
        $this->commandLine->runQuietly($cmd)->willReturn(".,\nmagento,\nsymfony");
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $this->commandLine->run("docker exec -u www-data m2-php test -e '.docker/composer-cache/repo'")->shouldBeCalled();
        $cmd = "docker exec -u www-data m2-php find .docker/composer-cache/repo/. -maxdepth 1 -newermt '2018-02-01 16:00' -exec basename {}, \\;";
        $this->commandLine->runQuietly($cmd)->willReturn(".,\nhttps---repo.magento.com,\nhttps---packagist.org");
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $this->commandLine->run("docker exec -u www-data m2-php test -e '.docker/composer-cache/vcs'")->shouldBeCalled();
        $cmd = "docker exec -u www-data m2-php find .docker/composer-cache/vcs/. -maxdepth 1 -newermt '2018-02-01 16:00' -exec basename {}, \\;";
        $this->commandLine->runQuietly($cmd)->willReturn(".,\nhttps---repo.magento.com,\nhttps---packagist.org");
        $this->commandLine->runQuietly($cmd)->shouldBeCalled();

        $modifiedFiles = [
            'vendor/magento',
            'vendor/symfony',
            '.docker/composer-cache/files/magento',
            '.docker/composer-cache/files/symfony',
            '.docker/composer-cache/repo/https---repo.magento.com',
            '.docker/composer-cache/repo/https---packagist.org',
            '.docker/composer-cache/vcs/https---repo.magento.com',
            '.docker/composer-cache/vcs/https---packagist.org',
            'composer.lock'
        ];
        $expectedInput = new ArrayInput(['files' => $modifiedFiles]);
        $this->pullCommand->run($expectedInput, $this->output)->shouldBeCalled();
    }
}
