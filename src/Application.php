<?php

namespace Jh\Workflow;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Application extends \Symfony\Component\Console\Application
{
    private $fallbackCommand = 'magento';

    /**
     * @param string $commandName
     * @return void
     */
    public function setFallBackCommand(string $commandName)
    {
        $this->fallbackCommand = $commandName;
    }

    /**
     * @inheritdoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        try {
            $this->setCatchExceptions(false);
            return parent::run($input, $output);
        } catch (CommandNotFoundException $e) {
            $arguments = $_SERVER['argv'];
            array_splice($arguments, 1, 0, $this->fallbackCommand);

            $input = new ArgvInput($arguments);

            try {
                return parent::run($input);
            } catch (\Exception $e) {
                return $this->exception($e, $output);
            }
        } catch (\Exception $e) {
            return $this->exception($e, $output);
        }
    }

    /**
     * @param \Exception $e
     * @param OutputInterface|null $output
     * @return int|mixed
     */
    private function exception(\Exception $e, OutputInterface $output = null)
    {
        $output = $output ?? new ConsoleOutput;

        if ($output instanceof ConsoleOutputInterface) {
            $this->renderException($e, $output->getErrorOutput());
        } else {
            $this->renderException($e, $output);
        }

        $exitCode = $e->getCode();
        if (is_numeric($exitCode)) {
            $exitCode = (int) $exitCode;
            if (0 === $exitCode) {
                $exitCode = 1;
            }
        } else {
            $exitCode = 1;
        }

        return $exitCode;
    }
}