<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class ChatbotService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 拉取订阅者
    public function getSubscriber()
    {
        // todo 

    }

}
