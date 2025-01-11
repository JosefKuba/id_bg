<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 将 csv 文件中的 ID 汇总去重
 *  支持多个文件
 */

class UpdateController extends CommandController
{

    private $updateNumber = "2";

    public function desc()
    {
        return [
            'command'   => 'php artisan id update',
            'desc'      => '将已经入库的ID值更新为2',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：更新刷脸合格的ID在 redis 中的值\n";
        echo "输入：data/id/input/  目录下的 id 文件，支持一次处理多个文件\n";
        echo "0号库 和 6号库 中的ID都会被更新\n";
        echo "\n";
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

        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput();

        // 1. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge();

        // 2. 处理ID
        $csvFiles = $fileService->getCsvFiles();

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $file = $csvFiles[0];
        $ids  = getLine($file);

        // 3. 更新0号库
        $count = 0;
        $redis = $this->getApp()->redis->getIdClient();
        foreach ($ids as $id) {
            $id = str_replace(["\n", "\r", "\r\n"], "", $id);
            if ($redis->exists($id)) {
                $redis->set($id, $this->updateNumber);
                $count++;
            }
        }

        // 4. 更新6号库
        $_count = 0;
        $redis  = $this->getApp()->redis->getMyIdClient();
        foreach ($ids as $id) {
            $id = str_replace(["\n", "\r", "\r\n"], "", $id);
            if ($redis->exists($id)) {
                $redis->set($id, $this->updateNumber);
                $_count++;
            }
        }

        $msg = sprintf("更新0号库ID %d 个，更新6号库ID %d 个", $count, $_count);

        // 记录更新时间
        $this->logger->log("id update " . $msg);

        $this->info($msg);

        $endTime = time();

        // 删除文件
        unlink($file);

        $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
