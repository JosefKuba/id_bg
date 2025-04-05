<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

/**
 * 根据小组标题，检测小组类型
 */

class TypeController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan group type',
            'desc'      => '根据小组的标题，将小组分类',
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
        $files = $fileService->getCsvFiles(GROUP_INPUT_PATH);

        if (empty($files)) {
            $this->error("input 目录下缺少文件");
            die;
        }

        // 2. 读取文件内容，匹配小组类型
        $groupService = $this->getApp()->group;
        $groupService->addType($files[0]);

        // 删除文件
        unlink($files[0]);
    }
}
