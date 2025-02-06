<?php

declare(strict_types=1);

namespace App\Command\Avater;

use Minicli\Command\CommandController;

class UnTestController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan avater untest',
            'desc'      => '放入一批ID，查询出还没有检测头像的ID',
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
