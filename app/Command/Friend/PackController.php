<?php

declare(strict_types=1);

namespace App\Command\Friend;

use Minicli\Command\CommandController;

class PackController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan friend pack',
            'desc'      => '将 pack.tsv 文件中的ID打包',
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
        // 定义打包的文件名称
        $packFile = FRIEND_INPUT_PATH . "pack.tsv";

        $friendService = $this->getApp()->friend;
        $friendService->pack($packFile);

        // 删除 input 目录下的文件
        unlink($packFile);
    }
}
