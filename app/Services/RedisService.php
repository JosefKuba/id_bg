<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class RedisService implements ServiceInterface
{
    private $app;

    public $client;

    // 台湾地区刷脸ID库
    // 由于刷脸筛掉的太多，重新刷脸，重新建立一个库进行排重
    // private const ID_DB_NUMBER = 0;
    private const ID_DB_NUMBER = 10;

    // 整理备份时用的库
    private const BACKUP_DB_NUMBER = 0;

    // 专页库[停用]
    private const PAGE_DB_NUMBER = 1;

    // 导深宗好友信仰ID库
    private const FRIENDS_DB_NUMBER = 2;

    // 小组ID库
    private const GROUPS_DB_NUMBER = 3;

    // 导过用户加入的小组的 用户ID 库
    private const GROUPS_USER_ID_DB_NUMBER = 4;

    // 检测信仰的ID库
    private const TESTING_FAITH_DB_NUMBER = 5;

    // 马来西亚线索ID库
    // private const MY_DB_NUMBER = 6;
    private const MY_DB_NUMBER = 11;

    // 检测马拉西亚信仰的ID库
    private const TESTING_MY_FAITH_DB_NUMBER = 7;

    // 检查刷脸不合格的ID库
    private const GIVE_UP_ID_DB_NUMBER = 9;

    public function load(App $app): void
    {
        $this->app = $app;
        $this->client = new \Predis\Client();
    }

    public function getClient()
    {
        return $this->client;
    }

    // 获取 ID 客户端
    public function getBackUpClient()
    {
        $this->client->select(self::BACKUP_DB_NUMBER);
        return $this->client;
    }

    // 获取 ID 客户端
    public function getIdClient()
    {
        $this->client->select(self::ID_DB_NUMBER);
        return $this->client;
    }

    // 获取马来ID客户端
    public function getMyIdClient()
    {
        $this->client->select(self::MY_DB_NUMBER);
        return $this->client;
    }

    // 获取 深宗好友 客户端
    public function getFriendsClient()
    {
        $this->client->select(self::FRIENDS_DB_NUMBER);
        return $this->client;
    }

    // 获取 专页 客户端
    public function getPageClient()
    {
        $this->client->select(self::PAGE_DB_NUMBER);
        return $this->client;
    }

    // 获取 小组 客户端
    public function getGroupClient()
    {
        $this->client->select(self::GROUPS_DB_NUMBER);
        return $this->client;
    }

    // 获取 导过用户加入的小组的 用户ID库 客户端
    public function getGroupUserIdClient()
    {
        $this->client->select(self::GROUPS_USER_ID_DB_NUMBER);
        return $this->client;
    }

    // 获取 检测信仰ID库 客户端
    public function getTestingFaithClient()
    {
        $this->client->select(self::TESTING_FAITH_DB_NUMBER);
        return $this->client;
    }

    // 获取 检测 马拉西亚 信仰ID库 客户端
    public function getTestingMyFaithClient()
    {
        $this->client->select(self::TESTING_MY_FAITH_DB_NUMBER);
        return $this->client;
    }

    // 获取 检测 马拉西亚 信仰ID库 客户端
    public function getGiveUpIdClient()
    {
        $this->client->select(self::GIVE_UP_ID_DB_NUMBER);
        return $this->client;
    }
}
