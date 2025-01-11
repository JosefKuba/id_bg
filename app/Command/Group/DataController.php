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

        $redis = $this->getApp()->redis->getGroupClient();

        $count = 0;
        foreach ($ids as $id) {
            if (!$redis->exists($id)) {
                $count++;
            }
        }

        $this->info((string)$count);
    }
}
