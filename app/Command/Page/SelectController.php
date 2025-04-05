<?php

declare(strict_types=1);

namespace App\Command\Page;

use Minicli\Command\CommandController;

class SelectController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan page select',
            'desc'      => '将小闪电搜索出来的专页，根据专页类型列进行分类，挑选出来宗派的和非宗派的类型',
        ];
    }

    public function help()
    {
        echo "专页类型位于第5列\n";
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

        // 
        $files = glob(PAGE_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        // 3. 处理数据文件
        $pageServce = $this->getApp()->page;
        $pageServce->selectReligion($files[0]);

        // 4. 清空 input 文件夹
        $fileService->clearFolder(PAGE_INPUT_PATH);
    }
}
