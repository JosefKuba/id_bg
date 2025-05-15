<?php

declare(strict_types=1);

namespace App\Command\Link;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan link',
            'desc'      => '将带参数的FB个人账号链接，处理成规范链接',
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
        $files = $fileService->getFiles(LINK_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        // 备份文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(LINK_INPUT_PATH);

        // 调整链接格式
        $linkService = $this->getApp()->link;
        $linkService->pure($files[0]);

        unlink($files[0]);
    }
}
