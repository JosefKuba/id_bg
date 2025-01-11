<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 处理 redis 数据
 */

class GiveupCheckController extends CommandController
{
    public function handle(): void
    {

        /*
            - 什么原则呢？
                - 好ID重复刷的原则
                - 这边的ID和250万已经刷过的ID放在一起去重，剩下的全部重新刷。
                - 
        */

        $redis   = $this->getApp()->redis->getGiveUpIdClient();
        $keys    = $redis->keys('*');

        $ids     = getLine(ID_INPUT_PATH . "ids");

        // $results = [];
        foreach ($keys as $key) {
            $count = 0;
            foreach ($ids as $id) {
                if ($redis->sIsMember($key, $id)) {
                    $count++;
                    // $results[$key][] = $id;
                    file_put_contents('tmp', $key . ' ' . $id . PHP_EOL, FILE_APPEND);
                }
            }

            echo $key . ' - ' . $count . PHP_EOL;
        }
    }
}
