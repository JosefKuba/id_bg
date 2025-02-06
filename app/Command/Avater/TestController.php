<?php

declare(strict_types=1);

namespace App\Command\Avater;

use Minicli\Command\CommandController;

class TestController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan avater test',
            'desc'      => '放入一批ID，查询出还没有检测头像的ID',
        ];
    }

    public function help()
    {
        echo "这是帮助手册" . PHP_EOL;
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
        $fileService = $this->getApp()->file;
        $files = $fileService->getCsvFiles(AVATER_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        // 备份文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(AVATER_INPUT_PATH);

        $avaterService = $this->getApp()->avater;

        // skipDB 参数控制是否要将每次检测的结果加入数据库
        $skipDB = $this->hasFlag("skip-db");
        $avaterService->test($files[0], $skipDB);

        unlink($files[0]);
    }
}
