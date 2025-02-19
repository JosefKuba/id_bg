<?php

declare(strict_types=1);

namespace App\Command\Friend;

use Minicli\Command\CommandController;

class DataController extends CommandController
{
    public function desc()
    {
        // return [
        //     'command'   => 'php artisan friend data',
        //     'desc'      => '将导出的好友，按照原始ID分割',
        // ];
    }

    public function help()
    {
        echo "这是帮助手册" . PHP_EOL;
    }

    public function handle(): void
    {
        if ($this->hasFlag("help")) {
            $this->help();
        } else {
            $this->exec();
        }
    }

    public function exec(): void
    {
        
        $ids = getLine(FRIEND_INPUT_PATH . "ids");

        $left = [];
        foreach ($ids as $id) {
            $files = glob(FRIEND_DB_FOLDER . $id . "*");
            if (!$files) {
                $left[] = $id;
            }
        }
        
        file_put_contents(FRIEND_INPUT_PATH . "result", implode(PHP_EOL, $left));
    }
}
