<?php

declare(strict_types=1);

namespace App\Command\Table;

use Minicli\Command\CommandController;

class DownloadChatbotController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan table downloadchatbot',
            'desc'      => '下载引流数据',
        ];
    }

    public function help()
    {
        echo '从chatbot同步表格中下载引流数据' . PHP_EOL;
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
        // $fileService = $this->getApp()->file;

        $tableService = $this->getApp()->table;

        // 下载 chatbot 引流表
        if (!$this->hasFlag("skip-download")) {
            $tableService->downloadChatbotTable();
        }

        // 统计每个帖文的引流数据
        $tableService->statisticPostEffect();
    }

    public function test() {
        
        // 接下来的处理思路是什么？
        // 1. 挑选出来最早的帖文
        // 2. 先整理备份的，再整理未备份的
        // 每个人的一次整理，然后合并
        // 后续的跳过
    }
}

