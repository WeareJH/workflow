<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Details;
use Jh\Workflow\FitProject\Details\Collector;
use Jh\Workflow\FitProject\Sequence\Executor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to retrofit an existing Magento root with Dockerfile
 * etc. for usability in standard JH local workflow. Handy when a project
 * already exists, rather than needing to create a new one.
 *
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class FitCommand extends Command implements CommandInterface
{
    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var Executor
     */
    private $executor;

    public function __construct(Collector $collector, Executor $executor)
    {
        $this->collector = $collector;
        $this->executor = $executor;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('fit')->setDescription('Retrofit an existing Magento root for Workflow compatibility');
        $this->collector->configure($this);
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Don\'t confirm values before taking action');

        $onlydesc = sprintf('Run a specific step only [%s]', implode(', ', $this->executor->getStepIds()));
        $this->addOption('only', null, InputOption::VALUE_REQUIRED, $onlydesc);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $data = new Details\Data();
        $style = new SymfonyStyle($input, $output);

        try {
            $this->collector->collect($input, $data, $style);
        } catch (Details\CollectorException $e) {
            $style->error($e->getMessage());
            return;
        }

        $style->title('Project configuration');
        $this->collector->display($data, $style);

        if (! $input->getOption('force')) {
            if ($style->confirm('Ready to fit?', true) !== true) {
                $style->error('Aborted fit');
                return;
            }
        }

        if ($step = $input->getOption('only')) {
            $this->executor->executeStep($step, $data, $style);
        }
        else {
            $this->executor->execute($data, $style);
        }

        $style->success(sprintf('Fitted existing project in %s', $data->getPath()));
    }
}
