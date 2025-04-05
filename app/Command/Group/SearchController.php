<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

class SearchController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan group search',
            'desc'      => '将搜索关键词搜出来的小组去重 入库 [查考组用]',
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
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(GROUP_INPUT_PATH);

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge(GROUP_INPUT_PATH);

        // 3. 处理数据文件
        $groupServce = $this->getApp()->group;
        $groupServce->handleSearchGroups();

        // 4. 清空 input 文件夹
        $fileService->clearFolder(GROUP_INPUT_PATH);
    }
}
