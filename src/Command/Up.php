<?php

namespace Jh\Workflow\Command;

use Jh\Workflow\CommandLine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Up extends Command implements CommandInterface
{
    /**
     * @var CommandLine
     */
    private $commandLine;

    public function __construct(CommandLine $commandLine)
    {
        parent::__construct();
        $this->commandLine = $commandLine;
    }

    public function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Uses docker-compose to start the containers')
            ->addOption('prod', 'p', InputOption::VALUE_OPTIONAL, 'Omits development configurations')
            ->addOption('mount', 'm', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Directories to mount at run time');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $envDockerFile = $input->getOption('prod')
            ? 'docker-compose.prod.yml'
            : 'docker-compose.dev.yml';

        $yaml = $this->generateYaml([
            'docker-compose.yml',
            $envDockerFile
        ], $input->getOption('mount'));

        // now echo the yaml and pipe into docker-compose
        // Note: This is because I couldn't get InputStream to work.
        $this->commandLine->run(sprintf('echo "%s" | docker-compose -f - up -d', $yaml));

        $output->writeln('<info>Containers started</info>');
    }

    /**
     * @param array $paths - docker-compose files to merge
     * @param array $mounts - paths to be converted into volumes
     * @return string
     */
    static function generateYaml($paths = [], $mounts = []) {
        $mergedYaml = collect($paths)
            ->map(function($path) {
                return Yaml::parse(file_get_contents($path));
            })
            ->reduce(function($carry, $arr) {
                return array_merge_recursive($carry, $arr);
            }, []);

        // do the merging that docker-compose would normally do
        // Hack because I don't know how to stop 'version' becoming an array because of 'array_merge_recursive' above
        $mergedYaml['version'] = '2';

        // convert path inputs to valid docker-volume definitions
        $dirs = collect($mounts)
            ->map(function($path) {
                return Path::canonicalize($path);
            })
            ->map(function($path) {
                return sprintf('./%s:/var/www/%s', $path, $path);
            })
            ->toArray();

        // merge the mounted paths with previous volumes
        $mergedYaml['services']['php']['volumes'] = array_merge($mergedYaml['services']['php']['volumes'], $dirs);

        return Yaml::dump($mergedYaml);
    }
}
