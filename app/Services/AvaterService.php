<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class AvaterService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    private $avaterClient;

    public function load(App $app): void
    {
        $this->app = $app;

        $this->avaterClient  = $this->app->redis->getAvaterClient();
    }

    // 录入检测的结果
    public function import ($file) 
    {

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
    public function getUntest($file) 
    {
        $lines = getLine($file);

        $unTestIds = [];

        $unTestCount = $testCount = 0;

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id = $lineArr[0];

            if (!$this->avaterClient->exists($id)) {
                $unTestIds[] = $id;
                $unTestCount++;
            } else {
                $testCount++;
            }
        }

        $path = AVATER_OUTPUT_PATH . CURRENT_TIME . " ids.tsv";
        file_put_contents($path, implode(PHP_EOL, $unTestIds));

        $total = count($lines);

        $this->app->info(sprintf(
            "ID 共计 %d 个, 已检测过 %d 个, 未检测 %d 个, 未检测的占比 %s",
            $total,
            $testCount,
            $unTestCount,
            number_format($unTestCount * 100 / $total, "1") . " %"
        ));
    }
}
