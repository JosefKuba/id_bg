<?php

declare(strict_types=1);

namespace App\Command\Faith;

use Minicli\Command\CommandController;

/**
 * 将 output 目录下的文件查询彩球标记
 *  该文件只能一行一个ID
 */

class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan faith',
            'desc'      => '将跑过信仰的账号按照信仰分类',
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
        $files = $fileService->getCsvFiles(FAITH_INPUT_PAHT);

        if (empty($files)) {
            $this->error("output 目录下没有文件");
            exit;
        }

        // 备份文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(FAITH_INPUT_PAHT);

        // 将原文件分类
        $faithService = $this->getApp()->faith;

        $type = $this->hasParam("type") ? $this->getParam("type") : "";
        $faithService->selectType($files[0], $type);

        unlink($files[0]);

        $this->success("分类完成");
    }
}
