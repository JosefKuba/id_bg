<?php

declare(strict_types=1);

namespace App\Command\List;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
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
        $commands = [
            'id' => [
                \App\Command\Id\DefaultController::class,
                \App\Command\Id\CollectController::class,
                \App\Command\Id\FriendController::class,
                \App\Command\Id\GroupController::class,
                \App\Command\Id\RmSelfController::class,
                \App\Command\Id\RepeatController::class,
            ],
            'friend' => [
                \App\Command\Friend\DefaultController::class,
                \App\Command\Friend\SelectController::class,
                \App\Command\Friend\PackController::class,
                \App\Command\Friend\RemoveController::class,
                \App\Command\Friend\IdFIlesController::class,
                \App\Command\Friend\IdsController::class,
            ],
            'rc'     => [
                \App\Command\RC\DefaultController::class,
                \App\Command\RC\LibController::class,
                \App\Command\RC\PackController::class,
                \App\Command\RC\CleanController::class,
            ],
            'avater'     => [
                \App\Command\Avater\DefaultController::class,
                \App\Command\Avater\ImportController::class,
                \App\Command\Avater\TestController::class,
            ],
            'redis'     => [
                \App\Command\Redis\DefaultController::class,
                \App\Command\Redis\StartController::class,
            ],
            'page'     => [
                \App\Command\Page\DefaultController::class,
                \App\Command\Page\TypeController::class,
            ],
            'group'     => [
                \App\Command\Group\DefaultController::class,
                \App\Command\Group\SearchController::class,
                \App\Command\Group\TypeController::class,
                \App\Command\Group\AreaController::class,
            ],
            'faith' => [
                \App\Command\Faith\DefaultController::class,
            ],
            'area' => [
                \App\Command\Area\DefaultController::class,
            ],
            'fish'  => [
                \App\Command\Fish\DefaultController::class,
                \App\Command\Fish\FanaticController::class,
            ],
            
        ];

        foreach ($commands as $command => $controllers) {

            if (empty($controllers)) {
                continue;
            }

            $this->info($command);

            foreach ($controllers as $controller) {

                if (!class_exists($controller)) {
                    continue;
                }

                $class = new $controller();

                if (!method_exists($class, "desc")) {
                    continue;
                }

                $descArr = $class->desc();

                echo $descArr['command'] . str_repeat(" ", 40 - strlen($descArr['command'])) . $descArr['desc'] . PHP_EOL;
            }
        }
    }
}
