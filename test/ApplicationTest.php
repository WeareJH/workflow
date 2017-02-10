<?php

use Jh\Workflow\Application;
use Jh\Workflow\Command\Magento;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationTest extends TestCase
{
    /**
     * @return void
     */
    public function testIfCommandDoesNotExistFallBackIsInvoked()
    {
        $app = new Application;
        $app->setAutoExit(false);
        $app->add(new class extends Command {
            protected function configure()
            {
                $this->setName('some-command');
            }
        });

        $fallback = $this->getMockBuilder(Magento::class)
            ->setConstructorArgs([new \Jh\Workflow\ProcessFactory])
            ->setMethods(['run'])
            ->getMock();

        $fallback
            ->expects($this->once())
            ->method('run')
            ->with(static::isInstanceOf(InputInterface::class), static::isInstanceOf(OutputInterface::class))
            ->willReturnCallback(function (InputInterface $input, $output) use ($fallback) {
                try {
                    $input->bind($fallback->getDefinition());
                } catch (ExceptionInterface $e) {
                }

                static::assertEquals($input->getArguments(), ['cmd' => 'magento']);
                return 0;
            });

        $app->add($fallback);

        $_SERVER['argv'] = ['workflow', 'some-command-that-does-not-exist', 'arg1'];

        $exitCode = $app->run();

        static::assertEquals(0, $exitCode);
    }
}
