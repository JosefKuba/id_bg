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
        $packFile = FRIEND_INPUT_PATH . "pack.tsv";

        $friendService = $this->getApp()->friend;
        $friendService->pack($packFile);

        unlink($packFile);
    }
}
