<?php

declare(strict_types=1);

namespace App\Command\Page;

use Minicli\Command\CommandController;

class TypeController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan page type',
            'desc'      => '将闪电导出的专页数据分类, 方便排查',
        ];
    }

    public function help()
    {
        echo "处理专页链接，按照专页类型和粉丝量进行分类\n";
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
        文件格式 tsv
        */

        $startTime = time();

        // 合并文件
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(PAGE_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(PAGE_INPUT_PATH);

        // 3. 处理数据文件
        $pageServce = $this->getApp()->page;
        // 区分是否是处理粉丝少的专页
        $pageServce->handleLikePage();

        // 4. 清空 input 文件夹
        $fileService->clearFolder(PAGE_INPUT_PATH);

        $endTime = time();

        // $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
