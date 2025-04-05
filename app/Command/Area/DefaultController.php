<?php

declare(strict_types=1);

namespace App\Command\Area;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan area',
            'desc'      => '挑选特定地区的ID',
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
        $files = $fileService->getCsvFiles(AREA_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        // 备份文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(AREA_INPUT_PATH);

        // 将原文件分类
        $faithService = $this->getApp()->area;
        $type = $this->hasParam("type") ? $this->getParam("type") : "";
        $faithService->selectArea($files[0], $type);

        unlink($files[0]);
    }
}
