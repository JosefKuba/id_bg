<?php

declare(strict_types=1);

namespace App\Command\Name;

use Minicli\Command\CommandController;

class BgController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan name bg',
            'desc'      => '挑选保加利亚名字',
        ];
    }

    public function help()
    {
        echo "这是帮助手册\n";
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
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(NAME_INPUT_PATH);

        $fileService = $this->getApp()->file;
        $files = $fileService->getCsvFiles(NAME_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        $nameServce = $this->getApp()->name;
        $nameServce->selectBgName($files[0]);

        unlink($files[0]);
    }
}
