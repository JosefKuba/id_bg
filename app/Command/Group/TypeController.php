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
            'desc'      => '根据小组或专页的中文标题，将小组专页进行分类',
        ];
    }

    public function help()
    {
        echo "根据中文名字进行分类，中文名字需要位于第一列. 将外语系的名字翻译成中文后，同样可以使用。" . PHP_EOL;
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
