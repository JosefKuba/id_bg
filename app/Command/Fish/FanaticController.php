<?php

declare(strict_types=1);

namespace App\Command\Fish;

use Minicli\Command\CommandController;

/**
 * 将 output 目录下的文件查询彩球标记
 *  该文件只能一行一个ID
 */

class FanaticController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan fish fanatic',
            'desc'      => '查询 🎈 💧 👤 牧师 ID',
        ];
    }

    public function help()
    {
        echo "这是帮助手册" . PHP_EOL;
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
        $startTime = time();

        $fishService = $this->getApp()->fish;
        $fishService->getFanatic(ID_OUTPUT_PATH . "result");
        $endTime = time();

        // $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
