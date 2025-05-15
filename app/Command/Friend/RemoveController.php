<?php

declare(strict_types=1);

namespace App\Command\Friend;

use Minicli\Command\CommandController;

class RemoveController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan friend remove',
            'desc'      => '删除 database/friends 目录下的ID文件',
        ];
    }

    public function help()
    {
        echo "输入：input 目录 id 文件" . PHP_EOL;
        echo "输出：无" . PHP_EOL;
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
        $startTime = time();

        $fileService = $this->getApp()->file;
        $files = $fileService->getFiles(FRIEND_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下缺少文件");
            die;
        }

        // 2. 读取文件内容，将含有关键字的名字匹配出来
        $friendService = $this->getApp()->friend;
        $count = $friendService->removeIdFiles($files[0]);

        unlink($files[0]);

        $endTime = time();

        $this->info(sprintf("移除文件 %d 个，耗时 %s 秒", $count, $endTime - $startTime));
    }
}
