<?php

declare(strict_types=1);

namespace App\Command\Redis;

use Minicli\Command\CommandController;

class StartController extends CommandController
{

    private $db;

    public function desc()
    {
        return [
            'command' => 'php artisan redis start',
            'desc'    => '根据 env 文件中的端口号，启动 redis server',
        ];
    }

    public function help()
    {
        echo "这是帮助文档" . PHP_EOL;
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
        $port     = $_ENV['REDIS_PORT'];
        $path     = ROOT_PATH . 'data/database/';
        $filename = 'dump.rdb';

        $command = sprintf("redis-server --port %d --dir %s --dbfilename %s", $port, $path, $filename);

        exec($command);
    }
}
