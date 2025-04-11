<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan group',
            'desc'      => '将闪电导出的用户加入的小组和总库 去重 入库',
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

        $files = glob(GROUP_INPUT_PATH . "*");
        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        // 3. 处理数据文件
        $groupServce = $this->getApp()->group;
        $groupServce->handleUserGroups($files[0]);

        // 4. 清空 input 文件夹
        unlink($files[0]);
    }
}
