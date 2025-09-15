<?php

declare(strict_types=1);

namespace App\Command\Sheet;

use Minicli\Command\CommandController;

class PostUniqueController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan sheet postunique',
            'desc'      => '发帖登记表数据去重',
        ];
    }

    public function help()
    {
        echo '....' . PHP_EOL;
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
        $fileService->clearFolder(SHEET_INPUT_PATH);

        $tableService = $this->getApp()->sheet;

        // 处理 发帖登记表 数据
        $tableService->postFillFormTableUnique();
    }
}

