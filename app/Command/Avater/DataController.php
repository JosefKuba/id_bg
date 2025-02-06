<?php

declare(strict_types=1);

namespace App\Command\Avater;

use Minicli\Command\CommandController;

class DataController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan data',
            'desc'      => '处理头像数据',
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
        $redisClient = $this->getApp()->redis->getAvaterClient();

        $path = AVATER_INPUT_PATH . "ids";
        $ids = getLine($path);

        // -1 代表还没有检测
        foreach ($ids as $id) {
            $redisClient->set($id, "-1");
        }

        $this->info("数据录入完成");
    }
}
