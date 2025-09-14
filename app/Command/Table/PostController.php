<?php

declare(strict_types=1);

namespace App\Command\Table;

use Minicli\Command\CommandController;

class PostController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan table post',
            'desc'      => '统计帖文引流量',
        ];
    }

    public function help()
    {
        echo '下载 chatbot 表格，并统计帖文的引流数量' . PHP_EOL;
    }

    public function handle(): void
    {
        if ($this->hasFlag("help")) {
            $this->help();
        } elseif ($this->hasFlag("test")) {
            $this->test();
        } else {
            $this->exec();
        }
    }

    public function exec(): void
    {
        // 先清空所有之前的记录
        $tableService = $this->getApp()->table;

        // 下载 chatbot 引流表
        if (!$this->hasFlag("skip-download")) {
            $tableService->downloadChatbotTable();
        }

        // 统计每个帖文的引流数据
        $tableService->statisticPostEffect();
    }

    public function test() {
        // todo
    }
}

