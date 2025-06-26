<?php

declare(strict_types=1);

namespace App\Command\Table;

use Minicli\Command\CommandController;

class BackupChatbotController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan table backupchatbot',
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
        $fileService->clearFolder(TABLE_INPUT_PATH);

        $tableService = $this->getApp()->table;

        $tableService->backupChatbotTable();
        
    }

    public function test() {
        
        $tableService = $this->getApp()->table;

        $files = glob(TABLE_OUTPUT_PATH . "*匈牙利*");

        // var_dump($files);die;

        $tableService->statisticChatbot($files);

        exit;
        

        // $str = "Thu Jun 20 2025 00:00:00 GMT+0200 (Midden-Europese zomertijd)";
        // $str = "Fri Jun 20 2025 00:00:00 GMT+0200 (Midden-Europese zomertijd)";
        // $str = "Thu Jun 19 2025 00:00:00 GMT+0200 (Midden-Europese zomertijd)";
        // $res = $tableService->daysSinceJsDate($str);
    
        // // todo 
        // echo $res . PHP_EOL;
    }
}

