<?php

declare(strict_types=1);

namespace App\Command\ZAP;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan zap',
            'desc'      => '整理搜索出来的zap结果，提取出zap小组链接',
        ];
    }

    public function help()
    {
        echo '先再Google中搜索 "keyword" "chat.whatsapp"，并在高级搜索中设置对应的语言。将搜索结果复制粘贴到表格之后，再用该工具进行处理' . PHP_EOL;
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
        $backupService->backupInput(ZAP_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(ZAP_INPUT_PATH);

        $csvFiles = $fileService->getFiles(ZAP_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $zapService = $this->getApp()->zap;
        $zapService->parse($csvFiles[0]);

        unlink($csvFiles[0]);
    }
}
