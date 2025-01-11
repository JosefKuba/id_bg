<?php

declare(strict_types=1);

namespace App\Command\Page;

use Minicli\Command\CommandController;

/**
 * 处理 redis 数据
 */

class DataController extends CommandController
{
    public function handle(): void
    {
        $pages = file(PAGE_DB_FILE);

        $redis = $this->getApp()->redis->getPageClient();

        foreach ($pages as $page) {
            $page = str_replace(["\r", "\n", "\r\n"], "", $page);
            $redis->set($page, "1");
            echo $page . PHP_EOL;
        }

        $this->info("数据录入完成");
    }
}
