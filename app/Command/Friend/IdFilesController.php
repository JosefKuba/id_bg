<?php

declare(strict_types=1);

namespace App\Command\Friend;

use Minicli\Command\CommandController;

class IdFilesController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan friend idfiles',
            'desc'      => '将导出的好友，按照原始ID分割成不同的文件',
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
        // 1. 读取一个目录下的所有 csv 文件
        $csvFiles = glob(ROOT_PATH . "_dev/id_toolbox/source/*");

        if (empty($csvFiles)) {
            $this->error("文件夹中缺少文件");
            exit();
        }

        // 拆分 ID 文件
        $friendService = $this->getApp()->friend;

        $total = count($csvFiles);
        foreach ($csvFiles as $key  => $file) {
            $startTime = time();
            $friendService->generateIdFile($file, false);
            $endTime = time();
            echo ($key + 1) . "/" . $total . " " . basename($file)  . " 耗时 " . ($endTime - $startTime) . " 秒" . PHP_EOL;

            unlink($file);
        }
    }
}
