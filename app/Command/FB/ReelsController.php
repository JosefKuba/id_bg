<?php

declare(strict_types=1);

namespace App\Command\FB;

use Minicli\Command\CommandController;

class ReelsController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan fb reels',
            'desc'      => '从FB的 html 结构中提取 reels 相关的信息',
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
        $csvFiles    = $fileService->getFiles(YTB_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $ytbServce = $this->getApp()->youtube;

        $ytbServce->shorts($csvFiles);
    }
}
