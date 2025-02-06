<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class RedisService implements ServiceInterface
{
    private $app;

    public $client;

    public $pubClient;

    // 刷脸ID库
    private $ID_DB_NUMBER;

    // 刷脸ID库
    private $ID_DB_NUMBER_2;
    
    // 用户的专页库
    private $PAGE_DB_NUMBER;

    // 用户的小组库
    private $USER_GROUP_DB_NUMBER;

    // 查考组的小组库
    private $SEARCH_GROUP_DB_NUMBER;

    // 头像库
    private $AVATER_DB_NUMBER;


    public function load(App $app): void
    {
        // 初始化数据库编号
        $this->ID_DB_NUMBER = $_ENV['ID_DB_NUMBER'];
        
        if ($_ENV['IS_DUBLE_FACE_DB'] == "true") {
            $this->ID_DB_NUMBER_2 = $_ENV['ID_DB_NUMBER_2'];
        }

        $this->PAGE_DB_NUMBER           = $_ENV['PAGE_DB_NUMBER'];
        $this->USER_GROUP_DB_NUMBER     = $_ENV['USER_GROUP_DB_NUMBER'];
        $this->SEARCH_GROUP_DB_NUMBER   = $_ENV['SEARCH_GROUP_DB_NUMBER'];

        $this->AVATER_DB_NUMBER = $_ENV['AVATER_DB_NUMBER'];

        $this->app = $app;

        // 本项目客户端
        $this->client = new \Predis\Client([
            'port'   => $_ENV['REDIS_PORT']
        ]);

        // 公用客户端
        $this->pubClient = new \Predis\Client([
            'port'   => $_ENV['PUBLIC_REDIS_PORT']
        ]);
    }

    public function getDesc () {
        
        $result = [];

        if ($_ENV['ID_DB_DESC']) {
            $result[$_ENV['ID_DB_NUMBER']] = $_ENV['ID_DB_DESC'];
        }

        if ($_ENV['IS_DUBLE_FACE_DB'] == "true" && $_ENV['ID_DB_DESC_2']) {
            $result[$_ENV['ID_DB_NUMBER_2']] = $_ENV['ID_DB_DESC_2'];
        }

        if ($_ENV['PAGE_DB_DESC']) {
            $result[$_ENV['PAGE_DB_NUMBER']] = $_ENV['PAGE_DB_DESC'];
        }

        if ($_ENV['USER_GROUP_DB_DESC']) {
            $result[$_ENV['USER_GROUP_DB_NUMBER']] = $_ENV['USER_GROUP_DB_DESC'];
        }

        if ($_ENV['SEARCH_GROUP_DB_DESC']) {
            $result[$_ENV['SEARCH_GROUP_DB_NUMBER']] = $_ENV['SEARCH_GROUP_DB_DESC'];
        }

        return $result;
    }

    public function getAvaterDesc() 
    {
        return [
            $_ENV['AVATER_DB_NUMBER'] => $_ENV['AVATER_DB_DESC'],
        ];
    }

    // 本地库客户端
    public function getClient()
    {
        return $this->client;
    }

    // 头像库的客户端
    public function getAvaterClient()
    {
        $this->pubClient->select($this->AVATER_DB_NUMBER);
        return $this->pubClient;
    }

    // 获取 ID 客户端
    public function getIdClient()
    {
        $this->client->select($this->ID_DB_NUMBER);
        return $this->client;
    }

    // 获取第二个客户端
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
}
