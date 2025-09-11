<?php

declare(strict_types=1);

namespace App\Command\Youtube;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan youtube',
            'desc'      => '根据关键词搜索YouTube视频',
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

        $keywords = getLine($csvFiles[0]);
        $ytbServce->search($keywords);

    }
}
