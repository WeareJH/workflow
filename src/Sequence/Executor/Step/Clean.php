<?php

namespace Jh\Workflow\Sequence\Executor\Step;

use Jh\Workflow\Details\DataInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Clean implements StepInterface
{
    /**
     * @var string[]
     */
    private $remove;

    public function __construct(array $remove = [])
    {
        $this->remove = $remove;
    }

    public function execute(DataInterface $data, StyleInterface $style)
    {
        $style->title("Removing unwanted files");

        $basePath = $data->getPath();

        foreach ($this->remove as $file) {
            $path = "{$basePath}/{$file}";
            if (file_exists($path)) {
                unlink($path);
                $style->text("{$file} deleted");
            } else {
                $style->warning("Cannot delete {$file} - file not found");
            }
        }

        $style->success("Removed unwanted files");
    }
}
