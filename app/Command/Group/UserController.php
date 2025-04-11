<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

class UserController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan group user',
            'desc'      => '检测小组成员所在地区的比例',
        ];
    }

    public function help()
    {
        echo <<<STRING

            安桑地区的ID有一部分需要从外邦小组中导出，该命令的作用是检测外邦小组中不同地区的人群比例。
        
            php artisan group area --ao      检测 安哥拉 小组
            php artisan group area --mz      检测 莫桑比克 小组
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
        /*
            需要有两个文件
                - 一个是不同的小组中导出的用户
                - 一个是用户的地区
        */
        
        $groups = GROUP_INPUT_PATH . "groups";
        $area   = GROUP_INPUT_PATH . "areas";

        $type = $this->getParam("type");
        if (empty($type) || !in_array($type, ["ao", "mz"])) {
            $this->error("缺少 type 参数");
            exit;
        }

        $groupServce = $this->getApp()->group;
        $groupServce->detectUser($groups, $area, $type);
    }
}
