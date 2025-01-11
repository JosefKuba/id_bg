<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class LanguageService implements ServiceInterface
{
    private $app;

    private $client;

    public function load(App $app): void
    {
        $this->app = $app;
        $this->client = new \LanguageDetector\LanguageDetector();
    }


    // 获取 ID 客户端
    public function getLangeuage($text)
    {
        return $this->client->evaluate($text)->getLangeuage();
    }
}
