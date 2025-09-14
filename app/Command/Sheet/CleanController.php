<?php

declare(strict_types=1);

namespace App\Command\Sheet;

use Minicli\Command\CommandController;

class CleanController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan sheet clean',
            'desc'      => '清理表格内容',
        ];
    }

    public function help()
    {
        echo '根据传入的URL和sheetName和startRow清理对应的分页' . PHP_EOL;
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

        // 清理表格数据
        $tableService->cleanTable();
    }

    public function test() {
        
        // 
    }
}

