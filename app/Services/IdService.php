<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class IdService implements ServiceInterface
{
    private $app;

    private $redisClient;

    private $db_file;

    private $type;

    private $secondFaceDBCode;

    public function load(App $app): void
    {
        $this->secondFaceDBCode = $_ENV["SECOND_FACE_DB_CODE"] ?? 'my';
        $this->app = $app;
    }

    private function setType($type)
    {
        $this->type = $type;
    }

    // 初始化客户端
    private function init($type): void
    {

        $this->setType($type);

        switch ($type) {
            case "friends":
                $this->redisClient  = $this->app->redis->getFriendsClient();
                $this->db_file      = FRIENDS_DB_FILE;
                break;

            case "groups":
                $this->redisClient  = $this->app->redis->getGroupUserIdClient();
                $this->db_file      = GROUPS_USER_ID_DB_FILE;
                break;

            case $this->secondFaceDBCode:
                $this->redisClient  = $this->app->redis->getIdClient_2();
                $this->db_file      = MY_ID_DB_FILE;
                break;

            default:
                $this->redisClient  = $this->app->redis->getIdClient();
                $this->db_file      = ID_DB_FILE;
        }
    }

    // 统计一个文件中的ID总数
    public function getAllIdsFromFile($filePath)
    {
        $content = file_get_contents($filePath);
        $lines = explode(PHP_EOL, $content);

        // 4. 处理ID
        $allIdArray = [];
        foreach ($lines as $line) {
            $line = str_replace(array("\r", "\n", "\r\n"), "", $line);
            if (empty($line)) {
                continue;
            }
            $ids = explode("\t", $line);
            foreach ($ids as $id) {
                $id = str_replace(array("\r", "\n", "\r\n"), "", $id);
                if (empty($id)) {
                    continue;
                }

                $allIdArray[] = $id;
            }
        }

        return $allIdArray;
    }

    // 获取文件中的ID总数
    public function getAllIdCountFromFile($filePath)
    {
        return count($this->getAllIdsFromFile($filePath));
    }

    // 读取一个文件中的内容，返回排重之后的结果数组
    public function getUniqueIdFromFile($filePath)
    {
        return array_unique($this->getAllIdsFromFile($filePath));
    }

    // 给一个ID文件，将该文件和总库排重，并将ID加入总库
    public function removeDuplicatesAndAddIntoTotal($path, $type = "")
    {
        $this->init($type);
        $this->removeDuplicatesInTotal($path);
        $this->addIdIntoTotal($path);
    }

    // 中途切换 redis 客户端会导致选择的库发生改变
    // 因此重置 redis 客户端
    private function resetInit()
    {
        $this->init($this->type);
    }

    // 给一个ID文件，将该文件和总库排重
    public function removeDuplicatesInTotal($path)
    {
        $content = file_get_contents($path);
        $ids     = explode(PHP_EOL, $content);

        $totalCount = count($ids);

        // 如果是 my 或者是 tw 需要和另一个库去重
        if (in_array($this->type, ["", $this->secondFaceDBCode]) && $_ENV['IS_DUBLE_FACE_DB'] == 'true') {
            switch ($this->type) {
                case $this->secondFaceDBCode:
                    $_redis = $this->app->redis->getIdClient();
                    break;
                default:
                    $_redis = $this->app->redis->getIdClient_2();
            }

            $_removeCount = 0;
            foreach ($ids as $key => $id) {
                $id = str_replace(["\r", "\n", "\r\n"], "", $id);
                if ($_redis->exists($id)) {
                    unset($ids[$key]);
                    $_removeCount++;
                }
            }

            // 重建索引
            $ids = array_values($ids);

            // 必须要写入文件，因为入库时，是读取的文件
            file_put_contents($path, implode(PHP_EOL, $ids));

            $_leftCount = count($ids);
            $this->app->info(sprintf("另一个库去重完成，重复 %d 个，剩余ID %d 个，不重复比例 %s", $_removeCount, $_leftCount, number_format($_leftCount * 100 / $totalCount, "1") . "%"));

            // 重置 redis 客户端
            $this->resetInit();
        }

        // 和本库去重
        $removeCount = 0;
        foreach ($ids as $key => $id) {
            $id = str_replace(["\r", "\n", "\r\n"], "", $id);
            if ($this->redisClient->exists($id)) {
                unset($ids[$key]);
                $removeCount++;
            }
        }

        file_put_contents($path, implode(PHP_EOL, $ids));

        $leftCount = count($ids);
        $this->app->info(sprintf("本库去重完成，和总库重复 %d 个，剩余ID %d 个，不重复比例 %s", $removeCount, $leftCount, number_format($leftCount * 100 / $totalCount, "1") . "%"));
    }

    // 给一个ID文件，将该文件中的ID加入总库
    private function addIdIntoTotal($path)
    {
        $content = file_get_contents($path);
        $ids = explode(PHP_EOL, $content);

        // 将id写入 redis
        foreach ($ids as $id) {
            $id = str_replace(["\r", "\n", "\r\n"], "", $id);
            $this->redisClient->set($id, "1");
        }

        // 将文件写入 ids
        $appendStr = PHP_EOL . "----" .  CURRENT_DATE . "----" . PHP_EOL . $content;
        file_put_contents($this->db_file, $appendStr, FILE_APPEND);

        $this->app->info("已将ID加入总库");
    }

    // 和 刷脸库 & 检测信仰库 排重
    // 并将新的 ID 加入检测信仰库
    public function dealFaithRepeat($file, $type = "")
    {
        $allIds = array_unique(\getLine($file));

        $newIds         = [];
        $repeatFaithIdsCount = 0;

        // 两个检测信仰库互相检测重复
        $otherRepeatCount = 0;
        switch ($type) {
            case $this->secondFaceDBCode:
                $_redisClient = $this->app->redis->getTestingFaithClient();
                break;

            default:
                $_redisClient = $this->app->redis->getTestingMyFaithClient();
        }

        foreach ($allIds as $key => $id) {
            if ($_redisClient->exists($id)) {
                unset($allIds[$key]);
                $otherRepeatCount++;
            }
        }

        $allIds = array_values($allIds);

        $this->app->info("和另一个检测信仰库重复 $otherRepeatCount 个");


        switch ($type) {
            case $this->secondFaceDBCode:
                $this->redisClient  = $this->app->redis->getTestingMyFaithClient();
                break;

            default:
                $this->redisClient  = $this->app->redis->getTestingFaithClient();
        }

        foreach ($allIds as $id) {
            if ($this->redisClient->exists($id)) {
                $repeatFaithIdsCount++;
            } else {
                // 将新 ID 加入总库
                $this->redisClient->set($id, "1");
                $newIds[] = $id;
            }
        }

        $this->app->info("已将新ID加入检测信仰库");

        return [
            'total'         => count($allIds),
            'new'           => array_values($newIds),
        ];
    }
}
