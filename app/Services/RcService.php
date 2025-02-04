<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class RcService implements ServiceInterface
{

    use Trait\SelectTrait;

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

    // 将安桑的ID进行分库
    public function ASLIb($file, $type) {

        $lines = getLine($file);

        // 去掉标题行
        array_shift($lines);

        // 检查文件格式 ： ID & 刷脸人
        $checkLineArr = explode("\t", $lines[5]);
        if (
            (!preg_match("/^\d+$/", $checkLineArr[1])) || 
            (!in_array($checkLineArr[3], $this->facePersion))
        ) {
            $this->app->error("未通过格式校验");
            exit;
        }


        // 截取所需要的列 名字 ID 来源渠道 所在地 家乡 最后发帖时间
        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);
            $lines[$key] = implode("\t", [$lineArr[0],$lineArr[1],$lineArr[2],$lineArr[4],$lineArr[5],$lineArr[6], CURRENT_DATE]);
        }

        // 先把自家专页的ID挑出来
        $selfPageLines = [];
        foreach ($lines as $line) {
            if (str_contains($line, "自家专")) {
                $selfPageLines[] = $line;
            }
        }

        // 在将时间早的ID挑出来
        $postEarlyLines = [];
        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);
            $lastPostTime = $lineArr[5];
            $isMatch = preg_match("/\d{4}/", $lastPostTime, $match);

            if (!$isMatch) {
                continue;
            }

            $year = $match[0];
            if ($year >= 2022) {
                continue;
            }

            $postEarlyLines[] = $line;

            unset($lines[$key]);
        }

        $lines = array_values($lines);

        // 根据地区分库
        $cities = match ($type) {
            'ao' => $this->aoCitys,
            'mz' => $this->mzCitys,
        };

        $wifiGoodLines = [];
        $wifiAverageLines = [];
        $wifiBadLines = [];
        $otherCityLines = [];

        foreach ($lines as $line) {
            $preg = "/" . implode("|", $cities['wifi_good']) . "/";
            if (preg_match($preg, $line)) {
                $wifiGoodLines[] = $line;
                continue;
            }

            $preg = "/" . implode("|", $cities['wifi_average']) . "/";
            if (preg_match($preg, $line)) {
                $wifiAverageLines[] = $line;
                continue;
            }

            $preg = "/" . implode("|", $cities['wifi_bad']) . "/";
            if (preg_match($preg, $line)) {
                $wifiBadLines[] = $line;
                continue;
            }

            $otherCityLines[] = $line;
        }

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " self page.tsv";
        file_put_contents($path, implode(PHP_EOL, $selfPageLines));

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " post early.tsv";
        file_put_contents($path, implode(PHP_EOL, $postEarlyLines));

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " wifi good.tsv";
        file_put_contents($path, implode(PHP_EOL, $wifiGoodLines));

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " wifi average.tsv";
        file_put_contents($path, implode(PHP_EOL, $wifiAverageLines));

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " wifi bad.tsv";
        file_put_contents($path, implode(PHP_EOL, $wifiBadLines));

        $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $type . " wifi other.tsv";
        file_put_contents($path, implode(PHP_EOL, $otherCityLines));
    }

}
