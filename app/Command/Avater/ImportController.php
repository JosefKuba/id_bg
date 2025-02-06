<?php

declare(strict_types=1);

namespace App\Command\Avater;

use Minicli\Command\CommandController;

class ImportController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan avater import',
            'desc'      => '录入检测头像的结果',
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
        // todo
    }
}
