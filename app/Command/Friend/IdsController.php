<?php

declare(strict_types=1);

namespace App\Command\Friend;

use Minicli\Command\CommandController;

class IdsController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan friend ids',
            'desc'      => '根据好友ID获取 friends_files_pure 目录下的ID',
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
        $files = $fileService->getFiles(FRIEND_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下缺少文件");
            die;
        }

        $friendService = $this->getApp()->friend;
        $friendService->getFriendFilesIds($files[0]);

        unlink($files[0]);
    }
}
