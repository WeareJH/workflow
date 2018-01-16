<?php

namespace Jh\Workflow;

/**
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aneurin "Anny" Barker Snook <anny@wearejh.com>
 */
class Template
{
    const TEMPLATE_DIR = __DIR__ . '/../templates/';

    public function cp($template, $dest)
    {
        $this->provision(dirname($dest));
        copy(static::TEMPLATE_DIR . $template, $dest);
    }

    private function provision($dest, $of = '/')
    {
        $path = '';
        $d = explode('/', ltrim($dest, '/'));
        foreach ($d as $dd) {
            $path .= "/{$dd}";
            if (! is_dir($path)) {
                mkdir($path);
            }
        }
    }

    public function repcp($template, $dest, array $replace)
    {
        $data = file_get_contents(static::TEMPLATE_DIR . $template);

        $before = array_map(function ($tag) {
            return "{{$tag}}";
        }, array_keys($replace));

        $data = str_replace($before, array_values($replace), $data);
        $this->write($dest, $data);
    }

    public function rgxcp($template, $dest, array $replace)
    {
        $data = file_get_contents(static::TEMPLATE_DIR . $template);
        foreach ($replace as $regex => $to) {
            $data = preg_replace($regex, $to, $data);
        }

        $this->write($dest, $data);
    }

    public function touch($dest)
    {
        $this->provision(dirname($dest));
        touch($dest);
    }

    public function write($dest, $data)
    {
        $this->provision(dirname($dest));
        file_put_contents($dest, $data);
    }
}
