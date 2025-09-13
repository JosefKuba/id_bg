<?php

declare(strict_types=1);

namespace App\Command\Table;

use Minicli\Command\CommandController;

class DownloadPostController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan table downloadpost',
            'desc'      => '从帖文一览表中下载专页和小组帖文',
        ];
    }

    public function help()
    {
        echo '从帖文一览表中下载专页和小组帖文' . PHP_EOL;
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
        $fileService = $this->getApp()->file;
        $fileService->clearFolder(TABLE_INPUT_PATH);

        $tableService = $this->getApp()->table;

        $tableService->downloadPost();

        $tableService->matchPostDetails();
    }

    public function test() {
        
        // 接下来的处理思路是什么？
        // 1. 挑选出来最早的帖文
        // 2. 先整理备份的，再整理未备份的
        // 每个人的一次整理，然后合并
        // 后续的跳过
    }
}

