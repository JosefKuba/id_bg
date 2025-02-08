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

            // 自家专页-新 都是活跃ID
            if ($year < 2022 && $lineArr[2] !== "自家专页-新") {
                $postEarlyLines[] = $line;
                unset($lines[$key]);
            }
        }

        $lines = array_values($lines);

        // 根据地区分库
        $cities = match ($type) {
            'ao' => $this->aoCitys,
            'mz' => $this->mzCitys,
        };

        $wifiGoodLines = [];
        $wifiAverageLines = [];
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

            // 包括网络差的地区ID 和 跑不出来地区的ID
            $otherCityLines[] = $line;
        }

        // 再从 otherCityLines 中，挑选出来 另一个国家的ID
        $otherCities = match ($type) {
            'ao' => $this->mzCitys,
            'mz' => $this->aoCitys,
        };

        $otherCountryWifiGoodLines    = [];
        $otherCountryWifiAverageLines = [];

        foreach ($otherCityLines as $key => $line) {
            $preg = "/" . implode("|", $otherCities['wifi_good']) . "/";
            if (preg_match($preg, $line)) {
                $otherCountryWifiGoodLines[] = $line;
                unset($otherCityLines[$key]);
                continue;
            }

            $preg = "/" . implode("|", $otherCities['wifi_average']) . "/";
            if (preg_match($preg, $line)) {
                $otherCountryWifiAverageLines[] = $line;
                unset($otherCityLines[$key]);
                continue;
            }
        }


        // todo 如果两边同时筛选自家专页，怎么办？
        $country = match ($type) {
            'ao' => '安哥拉',
            'mz' => '莫桑比克',
        };

        $otherCountry = match ($type) {
            'ao' => '莫桑比克',
            'mz' => '安哥拉',
        };


        if ($selfPageLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " 自家专页.tsv";
            file_put_contents($path, implode(PHP_EOL, $selfPageLines));
        }

        if ($postEarlyLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $country . " 时间早.tsv";
            file_put_contents($path, implode(PHP_EOL, $postEarlyLines));
        }

        if ($wifiGoodLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $country . " 网络好.tsv";
            file_put_contents($path, implode(PHP_EOL, $wifiGoodLines));
        }

        if ($wifiAverageLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $country . " 网络一般.tsv";
            file_put_contents($path, implode(PHP_EOL, $wifiAverageLines));    
        }

        if ($otherCityLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $country . " 其余地区.tsv";
            file_put_contents($path, implode(PHP_EOL, $otherCityLines));    
        }


        // 挑选出来另一个国家的ID 
        // 对于自家专页的旧库，是可以这样挑选的
        // 对于自家专页的新ID，这样挑选会有一些问题：其余地区的ID不一定是这个国家的
        // 但是没有更好的办法给区分开，只能用来源渠道来分开了

        if ($otherCountryWifiGoodLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $otherCountry . " 网络好.tsv";
            file_put_contents($path, implode(PHP_EOL, $otherCountryWifiGoodLines));    
        }

        if ($otherCountryWifiAverageLines) {
            $path = RC_OUTPUT_PATH . CURRENT_TIME . " " . $otherCountry . " 网络一般.tsv";
            file_put_contents($path, implode(PHP_EOL, $otherCountryWifiAverageLines));
        }
    }

    // 将 安哥拉 和 莫桑比克 两批ID 合并 & 压缩
    public function zip () {

        // 先检测 php 扩展是否安装
        if (!extension_loaded('zip')) {
            $this->app->error("Zip 扩展未安装, 请按照以下步骤按照 php-zip 扩展");
            echo 'sudo apt-get update' . PHP_EOL;
            echo 'sudo apt-get install php-zip' . PHP_EOL;
            exit;
        }

        // 检查要打包的格式，避免错误打包
        $files = glob(RC_OUTPUT_PATH . "*.tsv");
        
        foreach ($files as $file) {
            $basename = basename($file);
            $times[] = substr($basename, 0, 19);
        }

        $times = array_unique($times);
        if (count($times) > 2) {
            $this->app->error("打包时间点多于2个");
            exit;
        }

        // 合并文件
        $types = [
            "自家专页",

            "安哥拉 网络好",
            "安哥拉 网络一般",
            "安哥拉 其余地区",
            "安哥拉 时间早",
            
            "莫桑比克 网络好",
            "莫桑比克 网络一般",
            "莫桑比克 其余地区",
            "莫桑比克 时间早",
        ];

        $zipFolder = RC_OUTPUT_PATH . CURRENT_TIME . " merge/";
        if (!file_exists($zipFolder)) {
            mkdir($zipFolder);
        }

        foreach ($types as $type) {
            $files = glob(RC_OUTPUT_PATH . "*" . $type . ".tsv");

            // 将内容合并
            $outputName = $zipFolder . $type . ".tsv";
            $content = "";
            
            foreach ($files as $file) {
                $content .= file_get_contents($file) . PHP_EOL;
            }

            if ($content) {
                file_put_contents($outputName, $content);
            }

            echo sprintf("%s : 合并文件个数为 %d 个", $type, count($files)) . PHP_EOL;
        }

        // 压缩
        $zip = new \ZipArchive();

        foreach (["安哥拉", "莫桑比克"] as $country) {
            $zipFilename = $zipFolder . $country . " " . CURRENT_TIME . ".zip";

            if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
                exit("无法打开 <$zipFilename>\n");
            }

            $files = glob($zipFolder . $country . "*");
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            // 自家专页的ID打在 莫桑比克 的压缩包中
            if ($country === "莫桑比克") {
                $selfPageFile = $zipFolder . "自家专页.tsv";
                if (file_exists($selfPageFile)) {
                    $zip->addFile($selfPageFile, basename($selfPageFile));
                    echo '自家专页: 已打包在 莫桑比克 压缩包内' . PHP_EOL;
                } else {
                    echo '自家专页: 未找到，跳过打包' . PHP_EOL;
                }
            }

            $zip->close();
        }
    }
}
