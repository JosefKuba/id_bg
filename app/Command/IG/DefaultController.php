<?php

declare(strict_types=1);

namespace App\Command\IG;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan ig',
            'desc'      => '整理搜索出来的IG结果，提取出 粉丝量 和 专页名称',
        ];
    }

    public function help()
    {
        echo "在IG上搜索关键词，选择非个性化，结果用超链接抓取插件提取出来，之后再用该工具进行解析结果" . PHP_EOL;
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
        $backupService->backupInput(IG_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(IG_INPUT_PATH);

        $csvFiles = $fileService->getCsvFiles(IG_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $igService = $this->getApp()->ig;
        $igService->parse($csvFiles[0]);

        unlink($csvFiles[0]);
    }
}
