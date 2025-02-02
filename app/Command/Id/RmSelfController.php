<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 排除掉弟兄姊妹的账号
 */
class RmSelfController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id rmself',
            'desc'      => '排除弟兄姊妹的账号',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：排除弟兄姊妹的ID\n";
        echo "输入：data/id/input/ 目录下的 id 文件\n";
        echo "输出：data/id/input/ 新的文件\n";
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
        $startTime = time();

        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput();

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge();

        // 3. 处理ID
        $csvFiles = $fileService->getCsvFiles();

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $file = $csvFiles[0];

        // 排除弟兄姊妹的账号
        $fishService = $this->getApp()->fish;
        $fishService->removeDXZM($file);

        $endTime = time();

        // $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
