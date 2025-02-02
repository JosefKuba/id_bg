<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class RedisService implements ServiceInterface
{
    private $app;

    public $client;

    // 刷脸ID库
    private $ID_DB_NUMBER;

    // 刷脸ID库
    private $ID_DB_NUMBER_2;
    
    // 用户的专页库
    private $PAGE_DB_NUMBER;

    // 用户的小组库
    private $USER_GROUP_DB_NUMBER;

    private $SEARCH_GROUP_DB_NUMBER;

    // 整理备份时用的库
    // private const BACKUP_DB_NUMBER = 0;

    // 导深宗好友信仰ID库
    // private const FRIENDS_DB_NUMBER = 2;

    // 导过用户加入的小组的 用户ID 库
    // private const GROUPS_USER_ID_DB_NUMBER = 4;

    // 检测信仰的ID库
    // private const TESTING_FAITH_DB_NUMBER = 5;

    // 马来西亚线索ID库
    // private const MY_DB_NUMBER = 6;
    // private const MY_DB_NUMBER = 11;

    // 检测马拉西亚信仰的ID库
    // private const TESTING_MY_FAITH_DB_NUMBER = 7;

    // 检查刷脸不合格的ID库
    // private const GIVE_UP_ID_DB_NUMBER = 9;

    public function load(App $app): void
    {
        // 初始化数据库编号
        $this->ID_DB_NUMBER = $_ENV['ID_DB_NUMBER'];
        $this->ID_DB_NUMBER_2 = $_ENV['ID_DB_NUMBER_2'];
        $this->PAGE_DB_NUMBER = $_ENV['PAGE_DB_NUMBER'];
        $this->USER_GROUP_DB_NUMBER = $_ENV['USER_GROUP_DB_NUMBER'];
        $this->SEARCH_GROUP_DB_NUMBER = $_ENV['SEARCH_GROUP_DB_NUMBER'];

        $this->app = $app;
        $this->client = new \Predis\Client();
    }

    public function getDesc () {
        return [
            $_ENV['ID_DB_NUMBER'] => $_ENV['ID_DB_DESC'],
            $_ENV['ID_DB_NUMBER_2'] => $_ENV['ID_DB_DESC_2'],
            $_ENV['PAGE_DB_NUMBER'] => $_ENV['PAGE_DB_DESC'],
            $_ENV['USER_GROUP_DB_NUMBER'] => $_ENV['USER_GROUP_DB_DESC'],
            $_ENV['SEARCH_GROUP_DB_NUMBER'] => $_ENV['SEARCH_GROUP_DB_DESC'],
        ];
    }

    public function getClient()
    {
        return $this->client;
    }

    // 获取 ID 客户端
    public function getIdClient()
    {
        $this->client->select($this->ID_DB_NUMBER);
        return $this->client;
    }

    // 获取马来ID客户端
    public function getIdClient_2()
    {
        $this->client->select($this->ID_DB_NUMBER_2);
        return $this->client;
    }

    // 获取 专页 客户端
    public function getPageClient()
    {
        $this->client->select($this->PAGE_DB_NUMBER);
        return $this->client;
    }

    // 获取 用户的小组 客户端
    public function getUserGroupClient()
    {
        $this->client->select($this->USER_GROUP_DB_NUMBER);
        return $this->client;
    }
    
    // 获取 用户的小组 客户端
    public function getSearchGroupClient()
    {
        $this->client->select($this->SEARCH_GROUP_DB_NUMBER);
        return $this->client;
    }

    // ----------------------------------------

    // 获取 深宗好友 客户端
    // public function getFriendsClient()
    // {
    //     $this->client->select(self::FRIENDS_DB_NUMBER);
    //     return $this->client;
    // }

    // 获取 导过用户加入的小组的 用户ID库 客户端
    // public function getGroupUserIdClient()
    // {
    //     $this->client->select(self::GROUPS_USER_ID_DB_NUMBER);
    //     return $this->client;
    // }

    // 获取 检测信仰ID库 客户端
    // public function getTestingFaithClient()
    // {
    //     $this->client->select(self::TESTING_FAITH_DB_NUMBER);
    //     return $this->client;
    // }

    // 获取 检测 马拉西亚 信仰ID库 客户端
    // public function getTestingMyFaithClient()
    // {
    //     $this->client->select(self::TESTING_MY_FAITH_DB_NUMBER);
    //     return $this->client;
    // }

    // 获取 检测 马拉西亚 信仰ID库 客户端
    // public function getGiveUpIdClient()
    // {
    //     $this->client->select(self::GIVE_UP_ID_DB_NUMBER);
    //     return $this->client;
    // }
}
