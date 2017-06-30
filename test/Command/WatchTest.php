<?php

namespace Jh\WorkflowTest\Command;

use EventLoop\EventLoop;
use Jh\Workflow\Command\Watch;
use Jh\Workflow\Files;
use Jh\Workflow\WatchFactory;
use Prophecy\Argument;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\EventLoop\Timer\TimerInterface;
use Rx\Observable;
use Rx\React\FsWatch;
use Rx\Scheduler;
use Rx\Scheduler\EventLoopScheduler;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class WatchTest extends AbstractTestCommand
{
    /**
     * @var Watch
     */
    private $command;

    /**
     * @var WatchFactory
     */
    private $watchFactory;

    /**
     * @var Files
     */
    private $files;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var LoopInterface
     */
    private static $loop;

    public static function setUpBeforeClass()
    {
        self::$loop = new StreamSelectLoop;
        Scheduler::setDefaultFactory(function() {
            return new EventLoopScheduler(self::$loop);
        });
    }

    public function setUp()
    {
        parent::setUp();
        $this->watchFactory = $this->prophesize(WatchFactory::class);
        $this->files = $this->prophesize(Files::class);

        $this->command = new Watch($this->watchFactory->reveal(), $this->files->reveal());

        $this->fileSystem = new Filesystem;
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testCommandIsConfigured()
    {
        $description = 'Keeps track of filesystem changes, piping the changes to the sync command';

        static::assertEquals('watch', $this->command->getName());
        static::assertEquals([], $this->command->getAliases());
        static::assertEquals($description, $this->command->getDescription());
    }

    public function testWatchArgumentIsArrayAndOptional()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasArgument('watches'));
        static::assertTrue($definition->getArgument('watches')->isArray());
        static::assertFalse($definition->getArgument('watches')->isRequired());
    }

    public function testNoDefaultsOptionIsSetAndTakesNoValue()
    {
        $definition = $this->command->getDefinition();

        static::assertTrue($definition->hasOption('no-defaults'));
        static::assertFalse($definition->getOption('no-defaults')->acceptValue());
    }

    public function testWatchWithDefaultValues()
    {
        $this->input->getArgument('watches')->willReturn([]);
        $this->input->getOption('no-defaults')->willReturn(false);

        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->watchFactory
            ->create(
                ['app/code', 'app/design', 'composer.json', 'phpcs.xml', 'phpunit.xml'],
                ['".*__jb_.*$"', '".*swp$"', '".*swpx$"']
            )
            ->willReturn(Observable::empty());

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testWatchWithDefaultValuesAndDefinedWatches()
    {
        $this->input->getArgument('watches')->willReturn(['custom-dir']);
        $this->input->getOption('no-defaults')->willReturn(false);

        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->watchFactory
            ->create(
                ['custom-dir', 'app/code', 'app/design', 'composer.json', 'phpcs.xml', 'phpunit.xml'],
                ['".*__jb_.*$"', '".*swp$"', '".*swpx$"']
            )
            ->willReturn(Observable::empty());

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testNoDefaultsOptionRemovesDefaults()
    {
        $this->input->getArgument('watches')->willReturn(['custom-dir']);
        $this->input->getOption('no-defaults')->willReturn(true);

        $this->output->writeln('<info>Watching for file changes...</info>')->shouldBeCalled();
        $this->output->writeln('')->shouldBeCalled();

        $this->watchFactory
            ->create(
                ['custom-dir'],
                ['".*__jb_.*$"', '".*swp$"', '".*swpx$"']
            )
            ->willReturn(Observable::empty());

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testExceptionIsThrownWhenNoDefaultsSetAndNoArgumentsPassed()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->input->getArgument('watches')->willReturn([]);
        $this->input->getOption('no-defaults')->willReturn(true);

        $this->command->execute($this->input->reveal(), $this->output->reveal());
    }

    public function testCreatedDirectoryIsNotUploaded()
    {
        $this->useValidEnvironment();

        $folderToCreate = __DIR__ . '/../fixtures/valid-env/app/some-folder';

        $watch = (new WatchFactory(self::$loop))->create(['app']);
        $this->watchFactory->create(Argument::any(), Argument::any())->willReturn($watch);

        $this->command->run(new ArrayInput([]), new NullOutput);

        self::$loop->addTimer(1, function () use ($folderToCreate, $watch) {
            @mkdir($folderToCreate, 0777, true);

            self::$loop->addTimer(2, function () use ($watch) {
                self::$loop->stop();
                $watch->getSubject()->dispose();
            });
        });

        self::$loop->run();

        $this->files->upload('m2-php', [$folderToCreate])->shouldNotHaveBeenCalled();
        $this->files->delete('m2-php', [$folderToCreate])->shouldNotHaveBeenCalled();

        $this->fileSystem->remove(dirname($folderToCreate));
    }

    public function testNewFileIsUploaded()
    {
        $this->useValidEnvironment();

        @mkdir(__DIR__ . '/../fixtures/valid-env/app', 0777, true);
        $fileToCreate = __DIR__ . '/../fixtures/valid-env/app/file.php';

        $watch = (new WatchFactory(self::$loop))->create(['app']);
        $this->watchFactory->create(Argument::any(), Argument::any())->willReturn($watch);

        $this->command->run(new ArrayInput([]), new NullOutput);

        self::$loop->addTimer(1, function () use ($fileToCreate, $watch) {
            touch($fileToCreate);

            self::$loop->addTimer(2, function () use ($watch) {
                self::$loop->stop();
                $watch->getSubject()->dispose();
            });
        });

        self::$loop->run();

        $this->files->upload('m2-php', [realpath($fileToCreate)])->shouldHaveBeenCalled();
        $this->files->delete('m2-php', [realpath($fileToCreate)])->shouldNotHaveBeenCalled();

        $this->fileSystem->remove(__DIR__ . '/../fixtures/valid-env/app');
    }

    public function testNewFileIsDeleted()
    {
        $this->useValidEnvironment();

        @mkdir(__DIR__ . '/../fixtures/valid-env/app', 0777, true);
        $file = __DIR__ . '/../fixtures/valid-env/app/file.php';
        touch($file);
        $file = realpath($file);

        $watch = (new WatchFactory(self::$loop))->create(['app']);
        $this->watchFactory->create(Argument::any(), Argument::any())->willReturn($watch);

        $this->command->run(new ArrayInput([]), new NullOutput);

        self::$loop->addTimer(1, function () use ($file, $watch) {
            unlink($file);

            self::$loop->addTimer(2, function () use ($watch) {
                self::$loop->stop();
                $watch->getSubject()->dispose();
            });
        });

        self::$loop->run();

        $this->files->upload('m2-php', [$file])->shouldNotHaveBeenCalled();
        $this->files->delete('m2-php', [$file])->shouldHaveBeenCalled();

        $this->fileSystem->remove(__DIR__ . '/../fixtures/valid-env/app');
    }

    public function testNewFileIsCreatedAndDeleted()
    {
        $this->useValidEnvironment();

        @mkdir(__DIR__ . '/../fixtures/valid-env/app', 0777, true);
        $fileToCreate = __DIR__ . '/../fixtures/valid-env/app/create-me.php';
        $fileToDelete = __DIR__ . '/../fixtures/valid-env/app/delete-me.php';
        touch($fileToDelete);
        $fileToDelete = realpath($fileToDelete);

        $watch = (new WatchFactory(self::$loop))->create(['app']);
        $this->watchFactory->create(Argument::any(), Argument::any())->willReturn($watch);

        self::$loop->addTimer(1, function () use ($fileToDelete, $fileToCreate, $watch) {
            unlink($fileToDelete);
            touch($fileToCreate);

            self::$loop->addTimer(1, function () use ($watch) {
                self::$loop->stop();
                $watch->getSubject()->dispose();
            });
        });

        $this->command->run(new ArrayInput([]), new NullOutput);

        self::$loop->run();

        $this->files->upload('m2-php', [realpath($fileToCreate)])->shouldHaveBeenCalled();
        $this->files->delete('m2-php', [$fileToDelete])->shouldHaveBeenCalled();

        $this->fileSystem->remove(__DIR__ . '/../fixtures/valid-env/app');
    }

    public function testModifiedFileIsUploaded()
    {
        $this->useValidEnvironment();

        @mkdir(__DIR__ . '/../fixtures/valid-env/app', 0777, true);
        $fileToModify = __DIR__ . '/../fixtures/valid-env/app/file.php';
        touch($fileToModify);
        $fileToModify = realpath($fileToModify);

        $watch = (new WatchFactory(self::$loop))->create(['app']);
        $this->watchFactory->create(Argument::any(), Argument::any())->willReturn($watch);

        $this->command->run(new ArrayInput([]), new NullOutput);

        self::$loop->addTimer(1, function () use ($watch, $fileToModify) {
            file_put_contents($fileToModify, 'wow so much watch');

            self::$loop->addTimer(2, function () use ($watch) {
                self::$loop->stop();
                $watch->getSubject()->dispose();
            });
        });

        self::$loop->run();

        $this->files->upload('m2-php', [realpath($fileToModify)])->shouldHaveBeenCalled();
        $this->files->delete('m2-php', [realpath($fileToModify)])->shouldNotHaveBeenCalled();

        $this->fileSystem->remove(__DIR__ . '/../fixtures/valid-env/app');
    }
}
