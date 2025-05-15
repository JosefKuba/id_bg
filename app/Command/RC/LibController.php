<?php

declare(strict_types=1);

namespace App\Command\RC;

use Minicli\Command\CommandController;

class LibController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan rc lib type=ao|mz',
            'desc'      => '安桑ID入库前根据 来源渠道|家乡|所在地|最后发帖时间 进行分库',
        ];
    }

    public function help()
    {
        echo <<<STRING
            
            php artisan rc lib              自动检测地区 分库
            php artisan rc lib type=ao      检测安哥拉地区 分库
            php artisan rc lib type=mz      检测莫桑比克地区 分库
            \n
        STRING;
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
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput(RC_INPUT_PATH);

        $fileService = $this->getApp()->file;
        $csvFiles = $fileService->getFiles(RC_INPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $file = $csvFiles[0];

        // 根据文件类型自动选择 $type
        $type = $this->autoType($file);

        $rcServce = $this->getApp()->rc;
        $rcServce->ASLib($file, $type);

        unlink($file);
    }

    // 根据来源渠道自动选择 type
    private function autoType($file) {
        // 获取第三列的内容
        $lines = getLine($file);

        $channels = [];
        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $channels[] = $lineArr[2];
        }

        $channels = array_unique($channels);

        // 渠道中不能同时有 安哥拉 和 莫桑比克
        foreach ($channels as $channel) {
            if (str_contains($channel, "安哥拉") && str_contains($channel, "莫桑比克")) {
                $this->error("渠道中不能同时有 安哥拉 和 莫桑比克");
                exit;
            }
        }

        // 如果渠道中都没有含有 安哥拉 和 莫桑比克，则是自家专页新的ID，需要手动指定 type
        $hasCountryflag = false;
        foreach ($channels as $channel) {
            if (str_contains($channel, "安哥拉") || str_contains($channel, "莫桑比克")) {
                $hasCountryflag = true;
            }
        }

        // 在渠道中没有 安哥拉 和 莫桑比克 的情况下:
        if (!$hasCountryflag) {
            if (empty($this->getParam("type"))) {
                $this->error("来源渠道中没有检测到国家，需要手动指定 type");
                exit;
            } else {
                $type = $this->getParam("type");
                if (!in_array($type, ['ao', 'mz'])) {
                    $this->error("type类型有误, 只能为 ao mz 其中的一个");
                    exit;
                }

                return $type;
            }
        }

        foreach ($channels as $channel) {
            if (str_contains($channel, "安哥拉")) {
                return "ao";
            }

            if (str_contains($channel, "莫桑比克")) {
                return "mz";
            }
        }
    }
}

