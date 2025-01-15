<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;
use Minicli\Curly\Client;

class FishService implements ServiceInterface
{
    private $app;

    private $httpClient;

    private $chunkNumber  = 2000;

    private $markTimes = 5;

    public function load(App $app): void
    {
        $this->app = $app;
        $this->httpClient = new Client();
    }

    // 根据 ID 查询彩球标记
    public function getFish($filePath)
    {
        // 1. 读取需要过滤的文件
        // 这个逻辑应该是什么呢？先读取 output 中的文件，然后分批查询标记？
        $idContent = file_get_contents($filePath);
        $idLines = explode(PHP_EOL, $idContent);

        // 去掉换行符号
        $idLines = array_map(function ($id) {
            return str_replace(["\n", "\r", "\r\n"], "", $id);
        }, $idLines);

        $chunks = array_chunk($idLines, $this->chunkNumber);

        // 2. 发送请求获取标记

        $collectIds = [];   // 收集的ID
        $asideIds   = [];   // 暂缓的ID
        $excludeIds = [];   // 排除掉的ID

        $this->app->info(sprintf("开始查询彩球标记，共需查询 %s 次", count($chunks)));

        $endpoint = "http://localhost:1752/query";
        foreach ($chunks as $_key => $chunk) {

            $startTime = time();

            $paramters = [
                "platform"  => "facebook",
                "userID"    => "100087880793542",
                "userName"  => "李志",
                "ids"       => $chunk,
            ];

            $result = $this->httpClient->post($endpoint, $paramters);

            if ($result['code'] != 200) {
                $this->app->error("查询彩球标记失败");
                exit;
            }

            $fishes = json_decode($result['body'], true);

            if (array_key_exists("error", $fishes)) {
                $this->app->error($fishes["error"]);
                exit;
            }

            $excludeArray = [
                "internet",
                "response",
                // "fanatic", // 中深棕要保留
                "child",
                "religion",
                "espirit",
                "evil",
                "rumor",
                "hardtalk",
                "gentile",
                "findlover",
                "other",
            ];

            // 3. 将获取到的标记进行分类
            //  保留 🐟 👋 和 福音系统 的几个标记，副标记如何处理？
            $collectCount = 0;
            $asideCount = 0;
            $excludeCount = 0;

            // var_dump($chunk);
            // var_dump($fishes);
            // die;

            foreach ($chunk as $id) {

                // 先过滤掉 615 开头的账号，一般都是弟兄姊妹的账号
                if (preg_match("/^615/", $id) && strlen($id) == 14) {
                    $excludeIds[] = $id;
                    $excludeCount++;
                    continue;
                }

                // 没有标记的线索
                if (!array_key_exists($id, $fishes)) {
                    $collectIds[] = $id;
                    $collectCount++;
                    continue;
                }

                $fish = $fishes[$id];

                // if ($id == "61556099337697") {
                //     var_dump($fish);
                //     die;
                // }

                // 先排除不合格的主标记
                // var_dump($fish['status']);
                if (!in_array($fish['status'], [-1, 0, 1, 2, 3, 7, 8, 11, 20, 21, 22, 110])) {
                    $excludeIds[] = $id;
                    $excludeCount++;
                    continue;
                }

                // 再排除不合格的副标记 和 暂缓标记
                $keys = array_keys($fish);
                foreach ($keys as $key) {
                    if (in_array($key, $excludeArray)) {
                        $excludeIds[] = $id;
                        $excludeCount++;
                        continue 2;
                    }
                }

                // 再排除 🐟 的 天数 和 次数 限制
                if (in_array($fish['status'], [20, 21, 22])) {
                    if ($fish['number'] > $this->markTimes) {
                        $asideIds[] = $id;
                        $asideCount++;
                        continue;
                    }

                    // 排除有 👋 副标记的 ID
                    if (array_key_exists("sys_data", $fish)) {
                        if (array_key_exists("number", $fish["sys_data"])) {
                            if ($fish["sys_data"]["number"] > $this->markTimes) {
                                $asideIds[] = $id;
                                $asideCount++;
                                continue;
                            }
                        }
                    }
                }

                // 排除 👋 标记的 天数 和 次数 限制
                if ($fish['status'] == 110) {
                    if ($fish['number'] > $this->markTimes) {
                        $asideIds[] = $id;
                        $asideCount++;
                        continue;
                    }
                }

                $collectIds[] = $id;
                $collectCount++;
            }

            $endTime = time();

            $this->app->info(sprintf(
                "第 %d 次查询完成，合格ID %d 个，暂缓ID %d 个，排除ID %d 个。耗时 %d s",
                $_key + 1,
                $collectCount,
                $asideCount,
                $excludeCount,
                $endTime - $startTime
            ));

            if ($_key >= 450) {
                sleep(3);
            }
        }

        $percent = number_format(count($collectIds) * 100 / count($idLines), "1") . '%';
        $this->app->info(sprintf(
            "全部查询完成，ID共计 %d 个，合格ID %d 个，暂缓ID %d 个，排除ID %d 个，合格比例 %s",
            count($idLines),
            count($collectIds),
            count($asideIds),
            count($excludeIds),
            $percent
        ));

        return [
            'collect'   => $collectIds,
            'aside'     => $asideIds,
            'exclude'   => $excludeIds,
        ];
    }

    // 查询中深棕
    public function getFanatic($filePath)
    {
        // 1. 读取需要过滤的文件
        // 这个逻辑应该是什么呢？先读取 output 中的文件，然后分批查询标记？
        $idContent = file_get_contents($filePath);
        $idLines = explode(PHP_EOL, $idContent);

        // 去掉换行符号
        $idLines = array_map(function ($id) {
            return str_replace(["\n", "\r", "\r\n"], "", $id);
        }, $idLines);

        $chunks = array_chunk($idLines, $this->chunkNumber);

        // 2. 发送请求获取标记
        $this->app->info(sprintf("开始查询彩球标记，共需查询 %s 次", count($chunks)));

        $endpoint = "http://localhost:1752/query";
        foreach ($chunks as $_key => $chunk) {

            $paramters = [
                "platform"  => "facebook",
                "userID"    => "100087880793542",
                "userName"  => "李志",
                "ids"       => $chunk,
            ];

            $result = $this->httpClient->post($endpoint, $paramters);

            if ($result['code'] != 200) {
                $this->app->error("查询彩球标记失败");
                exit;
            }

            $fishes = json_decode($result['body'], true);

            // var_dump($fishes);
            // die;

            // 3. 将获取到的标记进行分类

            // 中深棕
            $fanatics = [];
            $fanaticsCount = 0;
            // 牧师
            $pastor = [];
            $pastorCount = 0;
            // 放弃
            $giveups = [];
            $giveupCount = 0;
            // 浇灌
            $waters = [];
            $waterCount = 0;

            // 标记次数多的🐟
            $_fishes = [];
            $fishCount = 0;

            // 标记次数多的👋
            $greetings = [];
            $greetingCount = 0;

            foreach ($chunk as $id) {

                $id = str_replace(["\n", "\r", "\r\n"], "", $id);

                // 先过滤掉 615 开头的账号，一般都是弟兄姊妹的账号
                // if (preg_match("/^615/", $id) && strlen($id) == 14) {
                //     continue;
                // }

                // 没有标记的线索
                if (!array_key_exists($id, $fishes)) {
                    continue;
                }

                $fish = $fishes[$id];

                // 中深棕
                if (array_key_exists("fanatic", $fish)) {
                    $fanatics[] = $id;
                    $fanaticsCount++;
                    continue;
                }

                // 牧师
                if (array_key_exists("reason", $fish) && $fish["reason"] === "牧师") {
                    $pastor[] = $id;
                    $pastorCount++;
                    continue;
                }

                // 💧 & 🏠 & 😊 
                if (!array_key_exists("sys_data", $fish)) {
                    if (in_array($fish["status"], [6, 9, 81])) {
                        $waters[] = $id;
                        $waterCount++;
                        continue;
                    }
                } else {
                    if (
                        array_key_exists("status", $fish["sys_data"]) &&
                        in_array($fish["sys_data"]["status"], [6, 9, 81])
                    ) {
                        $waters[] = $id;
                        $waterCount++;
                        continue;
                    }
                }

                // 🎈
                if (in_array($fish['status'], [4, 5])) {
                    $giveups[] = $id;
                    $giveupCount++;
                    continue;
                }



                // 收集标记次数多的🐟
                if (in_array($fish['status'], [20, 21, 22])) {
                    if ($fish['number'] > $this->markTimes) {
                        $_fishes[] = $id;
                        $fishCount++;
                        continue;
                    }

                    if (array_key_exists("sys_data", $fish)) {
                        if (array_key_exists("number", $fish["sys_data"])) {
                            if ($fish["sys_data"]["number"] > $this->markTimes) {
                                $greetings[] = $id;
                                $greetingCount++;
                                continue;
                            }
                        }
                    }
                }

                // 收集标记次数多的 👋
                if ($fish['status'] == 110) {
                    if ($fish['number'] > $this->markTimes) {
                        $greetings[] = $id;
                        $greetingCount++;
                        continue;
                    }
                }
            }

            $this->app->info(sprintf(
                "第 %d 次查询完成，中深棕 %d 个，牧师 %d 个，浇灌线索 %d 个，放弃线索 %d 个，鱼 %d 个，打招呼 %d 个",
                $_key + 1,
                $fanaticsCount,
                $pastorCount,
                $waterCount,
                $giveupCount,
                $fishCount,
                $greetingCount,
            ));

            if ($fanatics) {
                file_put_contents(ID_OUTPUT_PATH .  "fanatics", PHP_EOL . implode(PHP_EOL, $fanatics), FILE_APPEND);
            }

            if ($pastor) {
                file_put_contents(ID_OUTPUT_PATH .  "pastor", PHP_EOL . implode(PHP_EOL, $pastor), FILE_APPEND);
            }

            if ($waters) {
                file_put_contents(ID_OUTPUT_PATH .  "waters", PHP_EOL . implode(PHP_EOL, $waters), FILE_APPEND);
            }

            if ($giveups) {
                file_put_contents(ID_OUTPUT_PATH .  "giveups", PHP_EOL . implode(PHP_EOL, $giveups), FILE_APPEND);
            }

            if ($_fishes) {
                file_put_contents(ID_OUTPUT_PATH .  "fishes", PHP_EOL . implode(PHP_EOL, $_fishes), FILE_APPEND);
            }

            if ($greetings) {
                file_put_contents(ID_OUTPUT_PATH .  "greetings", PHP_EOL . implode(PHP_EOL, $greetings), FILE_APPEND);
            }

            sleep(2);
        }

        $this->app->info("全部查询完成");
    }


    // 过滤掉 弟兄姊妹 账号
    public function removeDXZM($filePath)
    {
        // 1. 读取需要过滤的文件
        // 这个逻辑应该是什么呢？先读取 output 中的文件，然后分批查询标记？
        $idContent = file_get_contents($filePath);
        $idLines = explode(PHP_EOL, $idContent);

        // 去掉换行符号
        $idLines = array_map(function ($id) {
            return str_replace(["\n", "\r", "\r\n"], "", $id);
        }, $idLines);

        $chunks = array_chunk($idLines, $this->chunkNumber);

        // 2. 发送请求获取标记
        $this->app->info(sprintf("开始查询彩球标记，共需查询 %s 次", count($chunks)));

        // 收集弟兄姊妹
        $notDXZM = [];
        $DXZM = [];

        $endpoint = "http://localhost:1752/query";
        foreach ($chunks as $_key => $chunk) {

            $paramters = [
                "platform"  => "facebook",
                "userID"    => "100087880793542",
                "userName"  => "李志豪",
                "ids"       => $chunk,
            ];

            $result = $this->httpClient->post($endpoint, $paramters);

            if ($result['code'] != 200) {
                $this->app->error("查询彩球标记失败");
                exit;
            }

            $fishes = json_decode($result['body'], true);

            $_notDXZM = [];
            $_DXZM = [];

            foreach ($chunk as $id) {

                // 过滤掉 615 开头的线索
                if (preg_match("/^615/", $id)) {
                    $_DXZM[] = $id;
                    $DXZM[] = $id;
                    continue;
                }

                // 没有标记的线索
                if (!array_key_exists($id, $fishes)) {
                    continue;
                }

                $fish = $fishes[$id];

                // 排除弟兄姊妹 和 其余的放弃标记
                // {62: '🈲', 63: '👿', 66: '🏳️‍🌈', 67: '🅾️', 68: '🤡' }
                if (
                    in_array($fish['status'], [80, 81, 60, 61, 62, 63, 66, 67, 68, 100]) ||
                    array_key_exists('religion', $fish) ||
                    array_key_exists('espirit', $fish) ||
                    array_key_exists('evil', $fish) ||
                    array_key_exists('gentile', $fish) ||
                    array_key_exists('findlover', $fish) ||
                    array_key_exists('hardtalk', $fish) ||
                    array_key_exists('other', $fish)
                ) {
                    $_DXZM[] = $id;
                    $DXZM[] = $id;
                } else {
                    $_notDXZM[] = $id;
                    $notDXZM[] = $id;
                }
            }

            if ($_DXZM) {
                file_put_contents(ID_OUTPUT_PATH . "ids_rmself", PHP_EOL . implode(PHP_EOL, $_DXZM), FILE_APPEND);
            }

            $this->app->info(sprintf("第 %d 次查询完成，弟兄姊妹账号 %d 个，剩余 %d 个", $_key + 1, count($_DXZM), count($_notDXZM)));

            sleep(2);
        }

        if ($notDXZM) {
            file_put_contents($filePath, PHP_EOL . implode(PHP_EOL, $notDXZM));
        }

        $this->app->info(sprintf(
            "全部查询完成，ID 共 %d 个，DZ账号 %d 个，保留ID %d 个",
            count($idLines),
            count($DXZM),
            count($notDXZM),
        ));
    }
}
