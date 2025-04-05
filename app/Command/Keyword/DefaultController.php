<?php

declare(strict_types=1);

namespace App\Command\Keyword;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan keyword',
            'desc'      => '从文本中拆分关键字，按空格拆分，各语系都可以使用',
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
        $files = $fileService->getCsvFiles(KEYWORD_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下没有文件");
            exit;
        }

        $keywordServices = $this->getApp()->keyword;
        $keywordServices->parse($files[0]);

        unlink($files[0]);
    }
}
