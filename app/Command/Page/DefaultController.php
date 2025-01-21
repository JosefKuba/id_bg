<?php

declare(strict_types=1);

namespace App\Command\Page;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan page',
            'desc'      => '将闪电导出的专页和总库去重 入库',
        ];
    }

    public function help()
    {
        echo "找宗派专页时使用\n";
        echo "可以处理 专页相关专页 专页点赞专页 用户点赞的专页\n";
        echo "根据 [专页编号] 进行排重和入库\n";
        echo "文件格式: 专页标题    专页编号    专页名称\n";
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
        $backupService->backupInput(PAGE_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(PAGE_INPUT_PATH);

        // 3. 处理数据文件
        $pageServce = $this->getApp()->page;
        $pageServce->handleUserPages();

        // 4. 清空 input 文件夹
        $fileService->clearFolder(PAGE_INPUT_PATH);
    }
}
