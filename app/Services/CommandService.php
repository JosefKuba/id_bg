<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class CommandService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    public function isCommand($command)
    {
        global $argv;

        return $argv[1] === $command;
    }
}
