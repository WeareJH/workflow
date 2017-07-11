<?php

namespace Jh\WorkflowTest;

use Jh\Workflow\ProcessFactory;
use Jh\Workflow\Files;
use Jh\Workflow\Test\WorkflowTest;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FilesTest extends WorkflowTest
{

    /**
     * @var Files
     */
    private $files;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $containerDirectory = __DIR__ . '/fixtures/test-env';

    /**
     * @var LoopInterface
     */
    private $loop;
    
    public function setUp()
    {
        $this->bootDockerInstance();

        $this->output = $this->prophesize(OutputInterface::class);

        $this->files = new Files(
            new ProcessFactory($this->loop = new StreamSelectLoop),
            $this->output->reveal()
        );

        $this->fileSystem = new Filesystem;
    }

    public function containerDirectory(string $path = null) : string
    {
        return null === $path ? $this->containerDirectory : $this->containerDirectory . '/' . ltrim($path, '/');
    }

    private function bootDockerInstance()
    {
        chdir($this->containerDirectory);
        exec('docker build -t workflow-test -f app.php.dockerfile ./');
        exec('docker-compose up -d 2> /dev/null');
    }

    public function tearDown()
    {
        $this->loop->stop();
        $this->destroyDockerInstance();
    }

    private function destroyDockerInstance()
    {
        chdir($this->containerDirectory);
        exec('docker-compose rm -sfv 2> /dev/null');
    }

    /**
     * @expectedException \Jh\Workflow\ProcessFailedException
     */
    public function testDownloadThrowsExceptionIfFileDoesNotExist()
    {
        $this->files->download('m2-php', ['some/not-existing-file.php']);
        $this->loop->run();
    }

    public function testDownload()
    {
        $this->copyFileInToContainer(__DIR__ . '/fixtures/test-env-files/some-file.php', '/var/www/some-file.php');

        $this->files->download('m2-php', ['some-file.php']);
        $this->loop->run();

        self::assertFileExists('some-file.php');

        $this->output
            ->writeln("<info>Copied 'some-file.php' from container into './' on the host</info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file.php');
    }

    public function testDownloadWithAbsoluteLocalPath()
    {
        $this->copyFileInToContainer(__DIR__ . '/fixtures/test-env-files/some-file.php', '/var/www/some-file.php');

        $this->files->download('m2-php', [__DIR__ . '/fixtures/test-env/some-file.php']);
        $this->loop->run();

        self::assertFileExists('some-file.php');

        $this->output
            ->writeln("<info>Copied 'some-file.php' from container into './' on the host</info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file.php');
    }

    public function testDownloadWithMultipleFiles()
    {
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/some-file1.php');
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/some-file2.php');

        $this->files->download('m2-php', ['some-file1.php', 'some-file2.php']);
        $this->loop->run();

        self::assertFileExists('some-file1.php');
        self::assertFileExists('some-file2.php');

        $this->output
            ->writeln("<info>Copied 'some-file1.php' from container into './' on the host</info>")
            ->shouldHaveBeenCalled();

        $this->output
            ->writeln("<info>Copied 'some-file2.php' from container into './' on the host</info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file1.php');
        $this->fileSystem->remove('some-file2.php');
    }

    public function testDownloadCreatesParentDirectoryStructureIfItDoesNotExist()
    {
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/folder/some-file.php');

        $this->files->download('m2-php', ['folder/some-file.php']);
        $this->loop->run();

        self::assertFileExists('folder/some-file.php');

        $this->output
            ->writeln("<info>Copied 'folder/some-file.php' from container into 'folder/' on the host</info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('folder');
    }

    public function testDelete()
    {
        $this->copyFileInToContainer(__DIR__ . '/fixtures/test-env-files/some-file.php', '/var/www/some-file.php');

        $this->files->delete('m2-php', ['some-file.php']);
        $this->loop->run();

        self::assertFileNotExistsInContainer('some-file.php', 'm2-php');
    }

    public function testDeleteWithAbsoluteLocalPath()
    {
        $this->copyFileInToContainer(__DIR__ . '/fixtures/test-env-files/some-file.php', '/var/www/some-file.php');

        $this->files->delete('m2-php', [__DIR__ . '/fixtures/test-env/some-file.php']);
        $this->loop->run();

        self::assertFileNotExistsInContainer('some-file.php', 'm2-php');
    }

    public function testDeleteWithMultipleFiles()
    {
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/some-file1.php');
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/some-file2.php');

        $this->files->delete('m2-php', ['some-file1.php', 'some-file2.php']);
        $this->loop->run();

        self::assertFileNotExistsInContainer('some-file1.php', 'm2-php');
        self::assertFileNotExistsInContainer('some-file2.php', 'm2-php');
    }

    public function testDeleteLocally()
    {
        touch('file1.txt');
        touch('file2.txt');

        self::assertFileExists('file1.txt');
        self::assertFileExists('file2.txt');

        $this->files->deleteLocally(['file1.txt', 'file2.txt']);

        self::assertFileNotExists('file1.txt');
        self::assertFileNotExists('file2.txt');
    }

    public function testDeleteLocallyWithAbsoluteLocalPath()
    {
        touch('file1.txt');
        touch('file2.txt');

        self::assertFileExists('file1.txt');
        self::assertFileExists('file2.txt');

        $this->files->deleteLocally(
            [$this->containerDirectory('file1.txt'), $this->containerDirectory('file2.txt')]
        );

        self::assertFileNotExists('file1.txt');
        self::assertFileNotExists('file2.txt');
    }

    public function testExistsInContainer()
    {
        $this->copyFileInToContainer(self::getFile('some-file.php'), '/var/www/some-file.php');

        self::assertTrue($this->files->existsInContainer('m2-php', 'some-file.php'));
        self::assertFalse($this->files->existsInContainer('m2-php', 'some-file-that-does-not-exist.php'));

        self::assertTrue($this->files->existsInContainer('m2-php', $this->containerDirectory('some-file.php')));
        self::assertFalse($this->files->existsInContainer('m2-php', $this->containerDirectory('some-file-that-does-not-exist.php')));
    }

    public function testUpload()
    {
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file.php'));
        $this->files->upload('m2-php', ['some-file.php']);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some-file.php', 'm2-php');

        $this->output
            ->writeln("<info> + 'some-file.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file.php');
    }

    public function testUploadWithAbsoluteLocalPath()
    {
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file.php'));
        $this->files->upload('m2-php', [$this->containerDirectory('some-file.php')]);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some-file.php', 'm2-php');

        $this->output
            ->writeln("<info> + 'some-file.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file.php');
    }

    public function testUploadMultipleFiles()
    {
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file1.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file2.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file3.php'));

        $this->files->upload('m2-php', ['some-file1.php', 'some-file2.php', 'some-file3.php']);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some-file1.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some-file2.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some-file3.php', 'm2-php');

        $this->output
            ->writeln("<info> + 'some-file1.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some-file2.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some-file3.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove('some-file1.php');
        $this->fileSystem->remove('some-file2.php');
        $this->fileSystem->remove('some-file3.php');
    }

    public function testUploadCreatesParentDirectoryStructureIfItDoesNotExist()
    {
        @mkdir($this->containerDirectory('/some/path'), 0777, true);
        @mkdir($this->containerDirectory('/some/path2'), 0777, true);
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path/some-file1.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path/some-file2.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path2/some-file3.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file4.php'));

        $this->files->upload('m2-php',[
            'some/path/some-file1.php',
            'some/path/some-file2.php',
            'some/path2/some-file3.php',
            'some-file4.php'
        ]);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some/path/some-file1.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some/path/some-file2.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some/path2/some-file3.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some-file4.php', 'm2-php');

        $this->output
            ->writeln("<info> + 'some/path/some-file1.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some/path/some-file2.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some/path2/some-file3.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some-file4.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove($this->containerDirectory('/some/path'));
        $this->fileSystem->remove($this->containerDirectory('/some/path2'));
        $this->fileSystem->remove('some-file4.php');
    }

    public function testUploadCreatesParentDirectoryStructureIfItDoesNotExistWithAbsolutePaths()
    {
        @mkdir($this->containerDirectory('/some/path'), 0777, true);
        @mkdir($this->containerDirectory('/some/path2'), 0777, true);
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path/some-file1.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path/some-file2.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path2/some-file3.php'));
        copy($this->getFile('some-file.php'), $this->containerDirectory('some-file4.php'));

        $this->files->upload('m2-php',[
            $this->containerDirectory('some/path/some-file1.php'),
            $this->containerDirectory('some/path/some-file2.php'),
            $this->containerDirectory('some/path2/some-file3.php'),
            $this->containerDirectory('some-file4.php')
        ]);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some/path/some-file1.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some/path/some-file2.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some/path2/some-file3.php', 'm2-php');
        self::assertFileExistsInContainer('/var/www/some-file4.php', 'm2-php');

        $this->output
            ->writeln("<info> + 'some/path/some-file1.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some/path/some-file2.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some/path2/some-file3.php' > m2-php </info>")
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln("<info> + 'some-file4.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove($this->containerDirectory('/some/path'));
        $this->fileSystem->remove($this->containerDirectory('/some/path2'));
        $this->fileSystem->remove('some-file4.php');
    }

    public function testParentDirectoryIsCreatedIfItDoesNotExistWhenUploadingSingleFile()
    {
        @mkdir($this->containerDirectory('/some/path'), 0777, true);
        copy($this->getFile('some-file.php'), $this->containerDirectory('some/path/some-file.php'));

        $this->files->upload('m2-php',[
            $this->containerDirectory('some/path/some-file.php'),
        ]);
        $this->loop->run();

        self::assertFileExistsInContainer('/var/www/some/path/some-file.php', 'm2-php');
        self::assertFileUserAndGroupInContainer('/var/www/some/path', 'www-data', 'www-data', 'm2-php');

        $this->output
            ->writeln("<info> + 'some/path/some-file.php' > m2-php </info>")
            ->shouldHaveBeenCalled();

        $this->fileSystem->remove($this->containerDirectory('/some/path'));
    }

    private static function getFile(string $filePath) : string
    {
        return __DIR__ . '/fixtures/test-env-files/' . $filePath;
    }
}
