<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\NewProject\DetailsGatherer;
use Jh\Workflow\NewProject\StepRunner;
use Jh\Workflow\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Aydin Hassan <aydin@>wearejh.com>
 * @author Michael Woodward <michael@wearejh.com>
 */
class NewProject extends Command implements CommandInterface
{
    /**
     * @var StepRunner
     */
    private $stepRunner;

    /**
     * @var DetailsGatherer
     */
    private $detailsGatherer;

    public function __construct(DetailsGatherer $detailsGatherer, StepRunner $stepRunner)
    {
        parent::__construct();

        $this->detailsGatherer = $detailsGatherer;
        $this->stepRunner      = $stepRunner;
    }

    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Magento 2 project');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);

        $details = $this->detailsGatherer->gatherDetails($output);

        try {
            $this->stepRunner->run($details, $output);
        } catch (ProcessFailedException $e) {
            throw $e;
        }

        $output->success(sprintf('%s Successfully Created', $details->getProjectName()));
    }
}
