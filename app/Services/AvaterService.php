<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class AvaterService implements ServiceInterface
{
    private $app;

    private $avaterClient;

    public function load(App $app): void
    {
        $this->app = $app; 
    }

    // 初始化 redis 客户端
    public function init() 
    {
        $this->avaterClient  = $this->app->redis->getAvaterClient();
    }

    // 录入检测的结果
    public function import ($file) 
    {
        $this->init();

        $lines = getLine($file);

        $errorLineCount = 0;
        $faceNumber = $notFaceNumber = 0;

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);

            $id = $lineArr[0] ?? "";
            $type = $lineArr[1] ?? "";

            if (empty($id) || empty($type)) {
                $errorLineCount++;
                continue;
            }

            if (str_contains($type, "不是")) {
                $notFaceNumber++;
                $avater = 0;
            } else {
                // 检测失败的也标记为1
                $faceNumber++;
                $avater = 1;
            }

            $this->avaterClient->set($id, $avater);
        }

        $total = count($lines);

        $this->app->info(sprintf(
            "ID 共 %d 个, 错误行 %d 个, 真人头像 %d 个，非真人头像 %d 个，真人头像占比 %s", 
            $total,
            $errorLineCount,
            $faceNumber,
            $notFaceNumber,
            number_format($faceNumber * 100 / $total, "1") . " %"
        ));
    }

    // 获取还没有测试的ID
    public function test($file, $skipDB)
    {
        $this->init();

        $lines = getLine($file);

        $allIds    = [];
        $unTestIds = [];
        $testIds   = [];

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id      = $lineArr[0];

            $allIds[] = $id;

            if (!$this->avaterClient->exists($id)) {
                // 为了避免多个批次之间重复检测头像，每检测一批头像，就把头像加入总库
                // 还未检测的状态是 -1
                if (!$skipDB) {
                    $this->avaterClient->set($id, "-1");
                }

                $unTestIds[] = $id;
            } else {
                $testIds[] = $id;
            }
        }

        $path = AVATER_OUTPUT_PATH . CURRENT_TIME . " untest ids.tsv";
        file_put_contents($path, implode(PHP_EOL, array_unique($unTestIds)));

        $totalIdsCount  = count($lines);
        $uniqueIdsCount = count(array_unique($allIds));

        $testIdsCount = count(array_unique($testIds));
        $unTestIdsCount = count(array_unique($unTestIds));

        $this->app->info(sprintf(
            "ID 共计 %d 个, 不重复ID %d 个, 已检测过 %d 个, 未检测 %d 个, 未检测的占比 %s",
            $totalIdsCount,
            $uniqueIdsCount,

            $testIdsCount,
            $unTestIdsCount,
            
            number_format($unTestIdsCount * 100 / $uniqueIdsCount, "1") . " %"
        ));
    }

    // 从给定ID中挑选出来是是人物头像的ID
    public function pick ($file) 
    {
        $this->init();

        $lines = getLine($file);

        $unTestIds = [];

        $faceIds = $notFaceIds = [];
        $untestIdsCount = $faceIdsCount = $notFaceIdsCount = 0;

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id = $lineArr[0];

            if (!$this->avaterClient->exists($id)) {
                $unTestIds[] = $id;
                $untestIdsCount++;
            } else {
                $res = $this->avaterClient->get($id);
                if ($res) {
                    $faceIds[] = $id;
                    $faceIdsCount++;
                } else {
                    $notFaceids[] = $id;
                    $notFaceIdsCount++;
                }
            }
        }

        $path = AVATER_OUTPUT_PATH . 'face/' . CURRENT_TIME . '.tsv';
        file_put_contents($path, implode(PHP_EOL, $faceIds));

        $path = AVATER_OUTPUT_PATH . 'notface/' . CURRENT_TIME . '.tsv';
        file_put_contents($path, implode(PHP_EOL, $notFaceIds));

        $path = AVATER_OUTPUT_PATH . 'untest/' . CURRENT_TIME . '.tsv';
        file_put_contents($path, implode(PHP_EOL, $unTestIds));
        
        $total = count($lines);

        $this->app->info(sprintf(
            "ID共计 %d 个, 人物头像ID %d 个, 非人物头像ID %d 个, 未检测ID %d 个. 人物头像比例 %s",
            $total,
            $faceIdsCount,
            $notFaceIdsCount,
            $untestIdsCount,
            number_format($faceIdsCount * 100 / $total, "1") . " %"
        ));
    }
}
