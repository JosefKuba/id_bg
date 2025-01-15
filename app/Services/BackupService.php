<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class BackupService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    /**
     * 将文件备份
     *  备份规则：
     *      - 文件名前面加上日期 和 时间
     *      - 之前在哪个文件夹下，就备份到 backup 中的哪个文件夹下
     */
    public function backupInput($path = ID_INPUT_PATH)
    {
        // 打印开始
        $this->app->info("备份开始...");

        // 1. 获取所有要备份的文件
        $files = glob($path . "*");

        switch ($path) {
            case ID_OUTPUT_PATH:
            case ID_INPUT_PATH:
                $backup_path = ID_BACKUP_PATH;
                break;
            case FRIEND_INPUT_PATH:
                $backup_path = FRIEND_BACKUP_PATH;
                break;
            case PAGE_INPUT_PATH:
                $backup_path = PAGE_BACKUP_PATH;
                break;
            case GROUP_INPUT_PATH:
                $backup_path = GROUP_BACKUP_PATH;
                break;
            case FAITH_INPUT_PAHT:
                $backup_path = FAITH_BACKUP_PAHT;
                break;
            case AREA_INPUT_PATH:
                $backup_path = AREA_BACKUP_PATH;
                break;
            case LINK_INPUT_PATH:
                $backup_path = LINK_BACKUP_PATH;
                break;
            case POST_INPUT_PATH:
                $backup_path = POST_BACKUP_PATH;
                break;
        }

        foreach ($files as $file) {

            if (!file_exists($file)) {
                echo $file . " 不存在";
                continue;
            }

            // 只备份文件
            if (!is_file($file)) {
                continue;
            }

            $name = basename($file);
            $newName = CURRENT_TIME  . ' ' . $name;

            copy($file, $backup_path . $newName);
        }

        $this->app->info("备份结束...");
    }

    public function test()
    {
        echo "test";
    }
}
