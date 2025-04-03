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
        $redis = $this->getApp()->redis->getIdClient();

        $files = glob(ID_INPUT_PATH . "*");
        $file  = $files[0];

        $ids   = getLine($file);

        $newIds = 0;
        foreach ($ids as $id) {
            if ($redis->exists($id)) {
                $redis->del($id);
                $newIds++;
            }
        }

        // $resultPath = ID_OUTPUT_PATH . basename($file) . ".new";
        // file_put_contents($resultPath, implode(PHP_EOL, $newIds));

        // unlink($file);

        $this->info(sprintf("新的ID %d 个", $newIds));
    }
}
