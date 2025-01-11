<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 处理 redis 数据
 */

class GiveupController extends CommandController
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

        $files = glob(ROOT_PATH . '_dev/check_bad/*');

        $filesCount = count($files);

        foreach ($files as $index => $file) {

            $startTime  = time();

            $lines = getLine($file);

            $names = explode(',', $lines[1]);

            foreach ($lines as $key => $line) {
                if ($key == 0 || $key == 1) {
                    continue;
                }

                $ids = explode(',', $line);

                foreach ($ids as $_key => $id) {
                    $name = $names[$_key];
                    // var_dump($id);
                    if (!empty($id)) {
                        $redis->sAdd($name, $id);
                    }
                }
            }

            $endTime = time();

            unlink($file);

            echo $index . ' / ' . $filesCount . ' - ' . ($endTime - $startTime) . PHP_EOL;
            // die;
        }
    }
}
