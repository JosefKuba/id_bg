<?php

declare(strict_types=1);

namespace App\Command\Avater;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan avater',
            'desc'      => '放入一批ID，查询出来有头像的ID',
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

        // 录入结果
        $avaterService = $this->getApp()->avater;
        $avaterService->pick($files[0]);

        unlink($files[0]);
    }
}
