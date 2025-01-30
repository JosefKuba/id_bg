<?php

declare(strict_types=1);

namespace App\Command\Group;

use Minicli\Command\CommandController;

/**
 * 处理 redis 数据
 */

class DataController extends CommandController
{
    public function handle(): void
    {
        $ids = getLine(GROUP_INPUT_PATH . "ids");

        $redis = $this->getApp()->redis->getUserGroupClient();

        $count = 0;
        foreach ($ids as $id) {
            if ($redis->exists($id)) {
                $redis->del($id);
                $count++;
            }
        }

        $this->info(sprintf("新ID %d 个" ,$count));
    }
}
