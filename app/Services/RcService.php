<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class RcService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 解析 RC tsv 文件
    public function parse($path)
    {
        $content = file_get_contents($path);
        $content = str_replace(array("\r", "\n", "\r\n"), "", $content);

        $contentArr = explode("\t", $content);

        $results = [];
        foreach ($contentArr as $item) {
            if (!str_contains($item, "|")) {
                continue;
            }

            $item = trim($item, "|");
            $item = str_replace("^", "", $item);

            $itemArr = explode("|", $item);
            $itemChunk = array_chunk($itemArr, 4);

            $results = array_merge($results, $itemChunk);
        }

        $output = "";
        $ids = [];
        foreach ($results as $item) {
            // if (
            //     str_contains($item[2], "佛") ||
            //     str_contains($item[2], "伊斯兰") ||
            //     str_contains($item[2], "印度")
            // ) {
            //     continue;
            // }

            $ids[] = $item[0] ?? "";

            $output .= implode("\t", $item) . PHP_EOL;
        }

        $rcPath = RC_OUTPUT_PATH . CURRENT_TIME .  " " . basename($path);
        file_put_contents($rcPath, $output);

        $idPath = RC_OUTPUT_PATH . CURRENT_TIME .  " " . str_replace(".tsv", ".id", basename($path));
        file_put_contents($idPath, implode(PHP_EOL, $ids));
    }
}
