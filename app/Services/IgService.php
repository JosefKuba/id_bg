<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class IgService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 解析 ig 搜索出来的内容
    public function parse ($file) {

        $lines = getLine($file);

        $results = [];
        foreach ($lines as $line) {

            // 避免分隔符的影响
            while (str_contains($line, "• •")) {
                $line = str_replace("• •", "•", $line);
            }

            // 繁体转化为简体
            $line = str_replace("萬", "万", $line);
            $line = str_replace("位粉絲", "位粉丝", $line);

            $lineArr = explode("\t", $line);
            $link = $lineArr[0];
            $desc = $lineArr[1];

            // $desc = "yuval_eliasi יובל אליאסי • מאמן כושר • יועץ תזונה • 1,833 位粉丝";

            if (!str_contains($desc, "•")) {
                $results[] = $line;
                continue;
            }

            $descArr = explode("•", $desc);

            var_dump($descArr);

            // 处理有多个 • 的情况:  "yuval_eliasi יובל אליאסי • מאמן כושר • יועץ תזונה • 1,833 位粉丝";
            $titleSlug =  trim($descArr[0]);
            $funsStr   = trim($descArr[1]);

            if (count($descArr) > 2) {
                $tmp = array_pop($descArr);
                $titleSlug = implode(" • ", $descArr);
                $funsStr = $tmp;
            }

            // slug & title
            preg_match('/^([a-zA-Z0-9_.]+)\s+(.*)$/u', $titleSlug, $matches);
            
            $slug = $matches[1];
            $title = $matches[2];
            
            // 粉丝量
            $funsStr = str_replace(",", "", $funsStr);
            if (str_contains($funsStr, "万")) {
                var_dump($funsStr);
                $count = str_replace(" 万 位粉丝", "", $funsStr);
                // var_dump($count);
                $funscount = $count * 10000;        
            } else {
                $funscount = str_replace(" 位粉丝", "", $funsStr);
            }

            $results[] = sprintf("%s\t%s\t%s\t%s\t%s", $link, $desc, $funscount, $title, $slug);
        }

        // todo fix
        $outputPath = IG_OUTPUT_PATH . CURRENT_DATE . " result";
        file_put_contents($outputPath, implode(PHP_EOL, $results));
    }


}
