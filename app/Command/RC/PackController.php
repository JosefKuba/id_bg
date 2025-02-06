<?php

declare(strict_types=1);

namespace App\Command\RC;

use Minicli\Command\CommandController;

class PackController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan rc zip',
            'desc'      => '将同一个国家的ID汇总压缩',
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
        $csvFiles = $fileService->getCsvFiles(RC_OUTPUT_PATH);

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $rcServce = $this->getApp()->rc;
        $rcServce->zip();
    }
}
