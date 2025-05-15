<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

class CheckFaithController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id checkfaith',
            'desc'      => '检测信仰ID: 排重 入库',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：将检测信仰的ID排重 入库\n";
        echo "输入：data/id/input/  目录下的 id 文件，支持一次处理多个文件\n";
        echo "输出：data/id/output/ 目录下的新的ID文件\n";
        echo "\t php artisan id checkfaith          入台湾库\n";
        echo "\t php artisan id checkfaith type=my  入马来库\n";
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

        // 和 检测信仰库 排重
        // 并将新的 ID 加入检测信仰库
        $idService = $this->getApp()->id;

        $uniqueIds = $idService->getUniqueIdFromFile($file);
        file_put_contents($file, implode(PHP_EOL, $uniqueIds));

        $type = $this->hasParam("type") ? $this->getParam("type") : "";
        $results = $idService->dealFaithRepeat($file, $type);

        // 保存结果
        file_put_contents(ID_OUTPUT_PATH . CURRENT_TIME . " faith new.tsv", implode(PHP_EOL, $results['new']));

        // 删除输入文件
        unlink($file);

        $this->info(sprintf("ID 共 %d 个", $results['total']));

        $percent = number_format(count($results['new']) * 100 / $results['total'], 1) . '%';
        $this->info(sprintf("新ID %d 个，剩存率 %s", count($results['new']), $percent));
    }
}
