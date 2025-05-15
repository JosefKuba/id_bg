<?php

declare(strict_types=1);

namespace App\Command\Post;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(POST_INPUT_PATH);

        $fileService = $this->getApp()->file;
        $files = $fileService->getFiles(POST_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        // 2. 处理数据文件
        $postServce = $this->getApp()->post;
        // 区分是否是处理粉丝少的专页
        $postServce->classify($files[0]);

        // 4. 清空 input 文件夹
        unlink($files[0]);
    }
}
