<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 处理 redis 数据
 */

class RepeatController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id repeat',
            'desc'      => '检查和库中重复的ID',
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
        $csvFiles = $fileService->getCsvFiles();

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $ids    = getLine($csvFiles[0]);
        $ids    = array_unique($ids);

        $redis  = $this->getApp()->redis->getIdClient();

        $newIds = [];
        $existsIds = [];
        foreach ($ids as $id) {
            if (!$redis->exists($id)) {
                $newIds[] = $id;
            } else {
                $existsIds[] = $id;
            }
        }

        $_newIds = [];
        $redis  = $this->getApp()->redis->getIdClient_2();
        foreach ($newIds as $id) {
            if (!$redis->exists($id)) {
                $_newIds[] = $id;
            } else {
                $existsIds[] = $id;
            }
        }

        file_put_contents(ID_OUTPUT_PATH . 'new result', implode(PHP_EOL, $_newIds));
        file_put_contents(ID_OUTPUT_PATH . 'exists result', implode(PHP_EOL, $existsIds));

        $this->info(sprintf("ID 共计 %d 个，新 ID %d 个", count($ids), count($_newIds)));
    }
}
