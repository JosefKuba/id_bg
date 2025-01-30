<?php

declare(strict_types=1);

namespace App\Command\Fish;

use Minicli\Command\CommandController;

/**
 * 将 output 目录下的文件查询彩球标记
 *  该文件只能一行一个ID
 */

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan fish',
            'desc'      => '查询彩球标记，并按照刷脸的标准分类',
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

        $fileService = $this->getApp()->file;
        $files = $fileService->getCsvFiles(ID_OUTPUT_PATH);

        foreach ($files as $key => $file) {
            if (is_dir($file)) {
                unset($files[$key]);
            }
        }

        $files = array_values($files);

        if (empty($files)) {
            $this->error("output 目录下没有文件");
            exit;
        }

        $outputFileName = $files[0];

        // 备份文件
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(ID_OUTPUT_PATH);

        // 查询彩球标记
        $fishService = $this->getApp()->fish;
        $fishResult = $fishService->getFish($outputFileName);

        // 将查询好的标记保存到文件中
        $collect_file_name = ID_OUTPUT_COLLECT_PATH . CURRENT_TIME . " " . basename($outputFileName);
        file_put_contents($collect_file_name, implode(PHP_EOL, $fishResult['collect']));

        $aside_file_name = ID_OUTPUT_ASIDE_PATH . CURRENT_TIME . " " . basename($outputFileName);
        file_put_contents($aside_file_name, implode(PHP_EOL, $fishResult['aside']));

        $exclude_file_name = ID_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . " " . basename($outputFileName);
        file_put_contents($exclude_file_name, implode(PHP_EOL, $fishResult['exclude']));

        unlink($outputFileName);

        $endTime = time();

        // $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
