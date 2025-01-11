<?php

declare(strict_types=1);

namespace App\Command\RC;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan rc',
            'desc'      => '将RC库下载的 tsv 文件格式化',
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
        $csvFiles = $fileService->getCsvFiles(RC_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        foreach ($csvFiles as $file) {
            $startTime = time();

            $rcServce = $this->getApp()->rc;
            $rcServce->parse($file);

            unlink($file);

            $endTime = time();

            $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
        }
    }
}
