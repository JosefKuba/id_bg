<?php

declare(strict_types=1);

namespace App\Command\Name;

use Minicli\Command\CommandController;

class ZhController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan name zh',
            'desc'      => '挑选中文名字',
        ];
    }

    public function help()
    {
        echo "挑选中文名字的线索，第一列是ID，第二列是名字" . PHP_EOL;
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
        // 1. 备份原始文件
        // $backupService = $this->getApp()->backup;
        // $backupService->backupInput(FRIEND_INPUT_PATH);

        $fileService = $this->getApp()->file;
        $files = $fileService->getCsvFiles(FRIEND_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下缺少文件");
            die;
        }

        // 2. 读取文件内容，将含有关键字的名字匹配出来
        $filesCount = count($files);
        foreach ($files as $key => $file) {
            $startTime = time();

            $friendService = $this->getApp()->friend;
            $friendService->selectName($file);

            unlink($file);

            $endTime = time();

            $this->info(sprintf("%d / %d : 挑选完成，耗时 %s 秒", ($key + 1), $filesCount, $endTime - $startTime));
        }
    }
}
