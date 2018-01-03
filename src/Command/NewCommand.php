<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\Details;
use Jh\Workflow\NewProject\Details\Collector;
use Jh\Workflow\NewProject\Sequence\Executor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Aydin Hassan <aydin@>wearejh.com>
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class NewCommand extends Command implements CommandInterface
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
        $this->setName('new')->setDescription('Create a new Magento 2 project');
        $this->collector->configure($this);
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Don\'t confirm values before taking action');
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
            if ($style->confirm('Ready to create project?', true) !== true) {
                $style->error('Aborted project creation');
                return;
            }
        }

        $this->executor->execute($data, $style);

        $style->success(sprintf('Created new project in %s', $data->getPath()));
    }
}
