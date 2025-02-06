<?php

declare(strict_types=1);

namespace App\Command\Redis;

use Minicli\Command\CommandController;
use Minicli\Output\Helper\TableHelper;
use Minicli\Output\Filter\ColorOutputFilter;

class DefaultController extends CommandController
{

    private $db;

    public function desc()
    {
        return [
            'command' => 'php artisan redis',
            'desc'    => '查看 redis 各个库中的 ID 数量',
        ];
    }

    public function help()
    {
        echo "各个库的作用说明如下：\n";
        foreach ($this->db as $dbNumber => $dbDesc) {
            echo "\t{$dbNumber}号库：{$dbDesc}\n";
        }
        echo "\n";
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
        // todo 
        if ($this->hasFlag("avater")) {
            $redisClient = $this->app->redis->getAvaterClient();
            $this->db =  $this->app->redis->getAvaterDesc();
            $port = $_ENV['PUBLIC_REDIS_PORT'];
            $desc = $_ENV['AVATER_DB_DESC'];
        } else {
            $redisClient = $this->app->redis->getClient();
            $this->db =  $this->app->redis->getDesc();
            $port = $_ENV['REDIS_PORT'];
            $desc = $_ENV['REDIS_DESC'];
        }

        $table = new TableHelper();
        
        $this->app->success(sprintf("目前使用的是 %s 数据库，端口: %d", $desc, $port)); 

        $table->addHeader(['编号', ' 数据量', '   描述']);

        // 获取各个库中的数据量
        foreach ($this->db as $dbNumber => $dbDesc) {
            $redisClient->select($dbNumber);
            $dbsize = $redisClient->dbsize();
            $table->addRow([
                (string)$dbNumber,
                (string)$dbsize . str_repeat(" ", 12 - strlen((string)$dbsize)),
                $dbDesc,
            ]);
        }

        $this->app->rawOutput($table->getFormattedTable(new ColorOutputFilter()));
        $this->app->newline();
        $this->app->newline();
    }
}
