<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\BufferWithTime;
use Jh\Workflow\Files;
use Rx\Observable;
use Rx\React\FsWatch;
use Rx\React\WatchEvent;
use Rx\Scheduler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Jh\Workflow\ProcessFactory;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Watch extends Command implements CommandInterface
{
    use DockerAwareTrait;

    /**
     * @var Files
     */
    private $files;

    public function __construct(ProcessFactory $processFactory, Files $files)
    {
        parent::__construct();
        $this->processFactory = $processFactory;
        $this->files = $files;
    }

    public function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Keeps track of filesystem changes, piping the changes to the sync command')
            ->addArgument('watches', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Custom paths to watch')
            ->addOption('no-defaults');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $watches  = ['app/code', 'app/design', 'composer.json', 'phpcs.xml', 'phpunit.xml'];
        $excludes = ['".*__jb_.*$"', '".*swp$"', '".*swpx$"'];

        $watches = $input->getOption('no-defaults')
            ? $input->getArgument('watches')
            : array_merge($input->getArgument('watches'), $watches);

        if (!$watches) {
            throw new \InvalidArgumentException('You must watch at least something...');
        }

        $output->writeln('<info>Watching for file changes...</info>');
        $output->writeln('');

        $phpContainer = $this->phpContainerName();

        $fsWatch = new FsWatch(implode(' ', $watches), implode(' -e ', $excludes) . ' -l 0.5', null);
        $fsWatch->lift(function () {
            return new BufferWithTime(500, Scheduler::getAsync());
        })->subscribe(function (array $watches) use ($phpContainer) {
            $files = collect($watches)
                ->reject(function (WatchEvent $event) {
                    return $event->isDir();
                })
                ->map(function (WatchEvent $event) {
                    return $event->getFile();
                });

            list($exists, $removed) = $files->partition(function ($item) {
                return file_exists($item);
            });

            $this->files->delete($phpContainer, $removed->all());
            $this->files->upload($phpContainer, $exists->all());
        });
    }
}
