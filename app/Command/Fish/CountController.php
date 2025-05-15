<?php

declare(strict_types=1);

namespace App\Command\Fish;

use Minicli\Command\CommandController;

/**
 * 将 output 目录下的文件查询彩球标记
 *  该文件只能一行一个ID
 */

class CountController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan fish count',
            'desc'      => '统计一批ID中鱼标记的次数',
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
        $fileService = $this->getApp()->file;
        $files       = $fileService->getFiles(ID_INPUT_PATH);

        if (empty($files)) {
            $this->error("output 目录下没有文件");
            exit;
        }

        $fishService = $this->getApp()->fish;
        $fishService->fishCount($files[0]);

        unlink($files[0]);
    }
}
