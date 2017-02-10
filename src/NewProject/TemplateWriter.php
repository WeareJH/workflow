<?php

namespace Jh\Workflow\NewProject;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class TemplateWriter
{
    const TEMPLATE_DIR = __DIR__ . '/../../templates/';

    public function copyTemplate(string $projectName, string $templatePath, string $projectDestPath)
    {
        copy(static::TEMPLATE_DIR . $templatePath, $projectName . '/' .$projectDestPath);
    }

    public function fillAndWriteTemplate(
        string $projectName,
        string $templateFile,
        string $outFile,
        array $searchAndReplace
    ) {
        $contents    = file_get_contents(static::TEMPLATE_DIR . $templateFile);
        $outFilePath = $projectName . '/' . $outFile;

        foreach ($searchAndReplace as $search => $replace) {
            $contents = str_replace('{' . $search . '}', $replace, $contents);
        }

        $this->writeContents($outFilePath, $contents);
    }

    public function regexFillAndWriteTemplate(
        string $projectName,
        string $templateFile,
        string $outFile,
        array $regextAndReplace
    ) {
        $contents    = file_get_contents(static::TEMPLATE_DIR . $templateFile);
        $outFilePath = $projectName . '/' . $outFile;

        foreach ($regextAndReplace as $regex => $replace) {
            $contents = preg_replace($regex, $replace, $contents);
        }

        $this->writeContents($outFilePath, $contents);
    }

    private function writeContents(string $path, string $contents)
    {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $contents);
    }
}
