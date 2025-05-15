<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 将所有的ID汇总
 */

class CollectController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id collect',
            'desc'      => '整理要导好友的 csv 格式的ID文件',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：整理 csv 格式的 ID 文件，并去重\n";
        echo "输入：data/id/input 目录下的 csv 文件，一次处理一个文件\n";
        echo "输出：data/id/output 目录下整理后的 id 文件\n";
        echo "\n";
    }

    public function handle(): void
    {
        if ($this->hasFlag("help")) {
            $this->help();
        } else {
            $this->exec();
        }
    }

    public function exec(): void
    {
        // ---------------- 汇总所有的ID --------------------
        $fileService = $this->getApp()->file;
        $fileService->merge(ID_INPUT_PATH, false);

        $csvFiles = $fileService->getFiles();

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $file = $csvFiles[0];

        $idService = $this->getApp()->id;
        $uniqueIds = $idService->getUniqueIdFromFile($file);

        // 末尾追加空行，方便文件合并
        $uniqueIds[] = "";

        $outputStr = implode(PHP_EOL, $uniqueIds);
        $outputFileName = ID_OUTPUT_PATH . CURRENT_TIME . " unique";
        file_put_contents($outputFileName, $outputStr);

        unlink($file);
    }
}
