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

        $redis = $this->getApp()->redis->getCustomerClient(9);

        $count = 0;
        $results = [];
        foreach ($ids as $id) {
            if (!$redis->exists($id)) {
                $redis->set($id, "1");
                $results[] = $id;
                $count++;
            }
        }

        file_put_contents("new", implode(PHP_EOL, $results));

        $this->info(sprintf("新ID %d 个" ,$count));
    }
}

