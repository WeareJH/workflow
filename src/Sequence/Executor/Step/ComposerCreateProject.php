<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\CommandLine;
use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class ComposerCreateProject implements StepInterface
{
    /**
     * @var CommandLine
     */
    private $cl;

    public function __construct(CommandLine $cl)
    {
        $this->cl = $cl;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $path = $data->getPath();
        $dirname = basename($path);

        $style->title("Creating composer project in {$dirname}");

        $pubkey  = $data->getMagentoPublicKey();
        $privkey = $data->getMagentoPrivateKey();
        $edition = $data->getMagentoEdition() == DataInterface::MAGENTO_EE ? 'enterprise' : 'community';

        $quiet = false ? '-q' : '';

        $command = <<<SHELL
composer create-project {$quiet} magento/project-{$edition}-edition {$path} \
--repository-url=https://{$pubkey}:{$privkey}@repo.magento.com/ \
--prefer-dist --ignore-platform-reqs
SHELL;

        $this->cl->run($command);
        $style->success("Composer project created in {$dirname}");
    }
}
