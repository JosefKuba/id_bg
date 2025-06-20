<?php

declare(strict_types=1);

namespace App\Command\Table;

use Minicli\Command\CommandController;

class GroupController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan table group',
            'desc'      => '处理小组的发帖和引流数据',
        ];
    }

    public function help()
    {
        echo '统计每个小组的 发帖量 和 引流量。下载 chatbot表 和 发帖登记表 在本地统计数据' . PHP_EOL;
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

        // 处理 发帖登记表 数据
        $tableService->handlePostFillFormTable();

        // 处理 chatbot表 数据
        $tableService->handleChatbotTable();
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

