<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class PathService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 根据绝对路径，获取相对路径
    public function getRelativePath($fullPath)
    {
        return str_replace(ROOT_PATH, "", $fullPath);
    }
}
