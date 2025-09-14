<?php

declare(strict_types=1);

namespace App\Command\Sheet;

use Minicli\Command\CommandController;

class BackupChatbotController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan sheet backupchatbot',
            'desc'      => '备份chatbot表格中的数据',
        ];
    }

    public function help()
    {
        echo '将表格中超过3个月的数据保存到云端的tsv文件中，减少表格的数据量' . PHP_EOL;
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

        $tableService->backupChatbotTable();
    }

    public function test() {
        // todo
    }
}

