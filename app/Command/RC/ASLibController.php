<?php

declare(strict_types=1);

namespace App\Command\RC;

use Minicli\Command\CommandController;

class ASLibController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan rc aslib',
            'desc'      => '安桑ID入库前根据 来源渠道|家乡|所在地|最后发帖时间 进行分库',
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

        $type = $this->getParam("type") ?: "ao";

        $rcServce = $this->getApp()->rc;
        $rcServce->ASLib($csvFiles[0], $type);

        // todo 
    }
}
