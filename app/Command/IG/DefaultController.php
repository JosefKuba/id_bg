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
            'desc'      => '整理搜索出来的IG结果，提取出 粉丝量和专页名称',
        ];
    }

    public function help()
    {
        echo "搜索关键词，选择非个性化，结果用超链接抓取插件提取出来，之后再进行处理" . PHP_EOL;
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
        /*
            处理的步骤：
                - 将个人的文件汇总
                - 按照 第四列 拆分 成一个个的文件
        */

        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(IG_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(IG_INPUT_PATH);

        // 3. 根据第四列拆分成一个个的小文件
        $csvFiles = $fileService->getCsvFiles(IG_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        // 拆分 ID 文件
        $igService = $this->getApp()->ig;
        $igService->parse($csvFiles[0]);

        // 删除文件
        unlink($csvFiles[0]);
    }
}
