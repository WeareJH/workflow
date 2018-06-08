<?php declare(strict_types=1);

namespace Jh\WorkflowTest\Command;

use Jh\Workflow\Command\GenerateConfig;
use Jh\Workflow\Config\ConfigGeneratorFactory;
use Jh\Workflow\Config\M1ConfigGenerator;
use Jh\Workflow\Config\M2ConfigGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class GenerateConfigTest extends TestCase
{
    /**
     * @var InputInterface|ObjectProphecy
     */
    private $input;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var GenerateConfig
     */
    private $command;

    /**
     * @var string
     */
    private $rootDir;

    public function setUp()
    {
        $this->rootDir = sys_get_temp_dir() . '/' . bin2hex(random_bytes(16));
        !mkdir($this->rootDir);

        $this->input  = $this->prophesize(ArgvInput::class);
        $this->output = new BufferedOutput();

        $this->input->getOption('root-dir')->willReturn($this->rootDir);

        $this->command = new GenerateConfig(new ConfigGeneratorFactory(
            new M1ConfigGenerator(),
            new M2ConfigGenerator()
        ));
    }

    public function getInputStream(array $inputs = [])
    {
        $stream = fopen('php://memory', 'rb+', false);

        fwrite($stream, implode(PHP_EOL, $inputs));
        rewind($stream);

        return $stream;
    }

    public function testM2DefaultGenerationWithNoInteraction()
    {
        $this->input->getOption('m1')->willReturn(false);
        $this->input->isInteractive()->willReturn(false);

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/env.default.php');
        $actual = file_get_contents($this->rootDir . '/app/etc/env.php');

        self::assertSame($expected, $actual);
    }

    public function testM2DefaultGenerationWithInteraction()
    {
        $this->input->getOption('m1')->willReturn(false);
        $this->input->isInteractive()->willReturn(true);
        $this->input->getStream()->willReturn($this->getInputStream(["\n", "\n"]));

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/env.default.php');
        $actual = file_get_contents($this->rootDir . '/app/etc/env.php');

        self::assertSame($expected, $actual);
    }

    public function testM2QueueGeneration()
    {
        $this->input->getOption('m1')->willReturn(false);
        $this->input->isInteractive()->willReturn(true);
        $this->input->getStream()->willReturn($this->getInputStream(['0', 'yes']));

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/env.queue.php');
        $actual = file_get_contents($this->rootDir . '/app/etc/env.php');

        self::assertSame($expected, $actual);
    }

    public function testM2ProductionGeneration()
    {
        $this->input->getOption('m1')->willReturn(false);
        $this->input->isInteractive()->willReturn(true);
        $this->input->getStream()->willReturn($this->getInputStream(['1', '0']));

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/env.production.php');
        $actual = file_get_contents($this->rootDir . '/app/etc/env.php');

        self::assertSame($expected, $actual);
    }

    public function testM1DefaultGeneration()
    {
        $this->input->getOption('m1')->willReturn(true);
        $this->input->isInteractive()->willReturn(false);

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/local.xml');
        $actual = file_get_contents($this->rootDir . '/app/etc/local.xml');

        self::assertSame($expected, $actual);
    }

    public function testM1DefaultGenerationWithHtdocsSetup()
    {
        $this->input->getOption('m1')->willReturn(true);
        $this->input->isInteractive()->willReturn(false);

        !mkdir($this->rootDir . '/htdocs');

        $this->command->execute($this->input->reveal(), $this->output);

        $expected = file_get_contents(__DIR__ . '/../fixtures/config/local.xml');
        $actual = file_get_contents($this->rootDir . '/htdocs/app/etc/local.xml');

        self::assertSame($expected, $actual);
        self::assertFileNotExists($this->rootDir . '/app/etc/local.xml');
    }

    public function testExceptionThrownWhenProjectRootDirDoesNotExist()
    {
        $this->expectException(\RuntimeException::class);

        rmdir($this->rootDir);

        $this->input->getOption('m1')->willReturn(true);
        $this->input->isInteractive()->willReturn(false);

        $this->command->execute($this->input->reveal(), $this->output);
    }

    public function testExceptionThrownWhenProjectRootDirIsNotADirectory()
    {
        $this->expectException(\RuntimeException::class);

        rmdir($this->rootDir);
        touch($this->rootDir);

        $this->input->getOption('m1')->willReturn(true);
        $this->input->isInteractive()->willReturn(false);

        $this->command->execute($this->input->reveal(), $this->output);
    }
}
