<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class FileService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 获取要处理的 csv 文件
    public function getCsvFiles($path = ID_INPUT_PATH)
    {
        return glob($path . "*");
    }

    // 清空文件夹
    public function clearFolder($path = ID_INPUT_PATH)
    {
        $files = glob($path . "*");
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * 将多个文件合并为一个文件
     */
    public function merge($path = ID_INPUT_PATH, $skipTwoLines = true)
    {
        $files = glob($path . "*");

        foreach ($files as $file) {
            $fileContent = file_get_contents($file);

            // 整理ID时，去掉每一个文件的前两行
            $commandService = new CommandService;
            if ($commandService->isCommand("id")) {
                $fileContentLines = explode(PHP_EOL, $fileContent);
                array_shift($fileContentLines);
                if ($skipTwoLines) {
                    array_shift($fileContentLines);
                }
                // 多个文件合并时，在每个文件前加空行
                $fileContent = PHP_EOL . implode(PHP_EOL, $fileContentLines);
            }

            $mergeName = $path . CURRENT_TIME . " merge";
            file_put_contents($mergeName, $fileContent, FILE_APPEND);
            unlink($file);
        }
    }
}
