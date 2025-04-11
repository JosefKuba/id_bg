<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

class AreaController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan group area',
            'desc'      => '按照地区分类以色列的小组，世俗派地区 阿拉伯语地区 俄语地区',
        ];
    }

    public function help()
    {
        echo <<<STRING

            小组名称位于第一列，小组地区位于第8列
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
        $files = glob(GROUP_INPUT_PATH . "*");
        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $groupServce = $this->getApp()->group;
        $groupServce->detectArea($files[0]);

        unlink($files[0]);
    }
}
