<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 临时处理备份数据
 */

class DataController extends CommandController
{
    public function handle(): void
    {
        // $redis = $this->getApp()->redis->getIdClient();

        // HR 跑地区库
        $redis = $this->getApp()->redis->getCustomerClient(1);

        $files = glob(ID_INPUT_PATH . "*");
        $file  = $files[0];

        $ids   = getLine($file);

        $newIds = 0;
        foreach ($ids as $id) {
            if (!$redis->exists($id)) {
                $redis->set($id, "1");
                $newIds++;
            }
        }

        $this->info(sprintf("新的ID %d 个", $newIds));
    }
}
