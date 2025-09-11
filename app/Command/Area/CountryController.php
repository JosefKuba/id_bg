<?php

declare(strict_types=1);

namespace App\Command\Area;

use Minicli\Command\CommandController;

class CountryController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan area country',
            'desc'      => '返回城市名称所对应的国家',
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
        $files = $fileService->getFiles(AREA_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        // 备份文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(AREA_INPUT_PATH);

        // 将原文件分类
        $areaService = $this->getApp()->area;
        $areaService->getCountry($files[0]);

        unlink($files[0]);
    }
}
