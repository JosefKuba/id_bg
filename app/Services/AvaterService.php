<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class AvaterService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }
}
