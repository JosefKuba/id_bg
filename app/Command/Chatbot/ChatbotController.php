<?php

declare(strict_types=1);

namespace App\Command\Chatbot;

use Minicli\Command\CommandController;

class ChatbotController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan chatbot sub',
            'desc'      => '从 chatot 中拉取订阅者',
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
        // $fileService = $this->getApp()->file;
        // $csvFiles    = $fileService->getFiles(YTB_INPUT_PATH);

        // if (empty($csvFiles)) {
        //     $this->error("input 文件夹中缺少文件");
        //     exit();
        // }

        // $ytbServce = $this->getApp()->youtube;

        // $ytbServce->shorts($csvFiles);
    }
}
