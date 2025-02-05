<?php

declare(strict_types=1);

namespace App\Command\RC;

use Minicli\Command\CommandController;

class CleanController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan rc clean',
            'desc'      => '清理RC库的 output 目录',
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
        $files = glob(RC_OUTPUT_PATH . "*");

        foreach ($files as $file) {
            // 跳过备份文件夹
            if (str_contains($file, "backup")) {
                continue;
            }

            rename($file, str_replace("output", "output/backup", $file));
        }
    }
}
