<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class FriendService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 提取ID
    public function generateIdFile($file, $fileNameDate = true)
    {
        $content = file_get_contents($file);
        $lines   = explode(PHP_EOL, $content);

        // 先检测是否包含四列
        $checkLine = $lines[10];
        $checkLineArr = explode("\t", $checkLine);
        if (count($checkLineArr) < 4) {
            echo '列数不满足要求, 跳过' . PHP_EOL;
            return;
        }

        // 为了避免重复贴ID，先将文件整体去重
        $lines = array_unique($lines);

        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);

            $friendId = trim($lineArr[0] ?? "", "'");
            $sourceId = str_replace(["\r", "\n", "\r\n"], "", $lineArr[3] ?? "");
            $sourceId = trim($sourceId, "'");

            if (empty($friendId) || empty($sourceId)) {
                unset($lines[$key]);
                continue;
            }

            if (!preg_match("/^\d+$/", $friendId) || !preg_match("/^\d+$/", $sourceId)) {
                unset($lines[$key]);
                continue;
            }

            $sourceIdArr[] = $sourceId;
        }

        // 根据原始ID进行排序
        $lines = array_values($lines);
        array_multisort($sourceIdArr, $lines);

        $_lines = [];
        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);

            $friendId = trim($lineArr[0] ?? "", "'");
            $sourceId = str_replace(["\r", "\n", "\r\n"], "", $lineArr[3] ?? "");
            $sourceId = trim($sourceId, "'");

            $_lines[$key] = [
                'friend_id' => $friendId,
                'source_id' => $sourceId,
            ];
        }

        $isError = false;
        $nextUseSourceId = false;
        $linesCount = count($lines);
        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);

            $friendId = trim($lineArr[0] ?? "", "'");
            $sourceId = str_replace(["\r", "\n", "\r\n"], "", $lineArr[3] ?? "");
            $sourceId = trim($sourceId, "'");

            // 处理最后一个ID
            if ($key + 1 !== $linesCount) {
                if ($_lines[$key + 1]['source_id'] - $_lines[$key]['source_id'] == 1) {
                    if ($isError === false) {
                        $_sourceId = $sourceId;
                    }
                    $isError = true;
                    $nextUseSourceId = true;
                } else {
                    if (!$nextUseSourceId) {
                        $_sourceId = $sourceId;
                    }
                    $isError = false;
                    $nextUseSourceId = false;
                }
            } else {
                if ($_lines[$key]['source_id'] - $_lines[$key - 1]['source_id'] == 1) {
                    if ($isError === false) {
                        $_sourceId = $sourceId;
                    }
                } else {
                    if (!$nextUseSourceId) {
                        $_sourceId = $sourceId;
                    }
                }
            }

            $lines[$key] = $friendId . "\t" . $lineArr[1] . "\t"  . "" . "\t" . $_sourceId;
        }

        $collectIds = [];
        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);

            $friendId = trim($lineArr[0] ?? "", "'");
            $sourceId = str_replace(["\r", "\n", "\r\n"], "", $lineArr[3] ?? "");
            $sourceId = trim($sourceId, "'");

            if (empty($friendId) || empty($sourceId)) {
                continue;
            }

            if (!preg_match("/^\d+$/", $friendId) || !preg_match("/^\d+$/", $sourceId)) {
                continue;
            }

            $collectIds[$sourceId][] = $friendId;
        }

        // 遍历数组，将数组转为数据文件
        foreach ($collectIds as $sourceId => $friendIds) {

            // 导到一半，电脑死机，又重复导。会导致ID重复，去重
            $friendIds = array_unique($friendIds);

            if ($fileNameDate) {
                $idFilePath = FRIEND_DB_FOLDER_TMP . $sourceId . " " . CURRENT_TIME;
            } else {
                $idFilePath = FRIEND_FILES_FOLDER . $sourceId;
            }

            // 重复导出的ID，后导的会覆盖前面的，因此能保证ID是新的
            file_put_contents($idFilePath, implode(PHP_EOL, $friendIds));
        }
    }

    // 打包
    public function pack($packFile): void
    {
        /*
            处理的步骤：
                - 根据输入的ID，逐个的对ID进行打包
        */

        if (!file_exists($packFile)) {
            $this->app->error("input 目录下缺少 pack.tsv 文件");
            exit;
        }

        $lines = file($packFile);

        $providerId = $lines[0];
        $providerIdArr = explode("\t", $providerId);

        // 创建一个记录索引的数组
        $indexArr = [];
        foreach ($providerIdArr as $key => $id) {
            $id = str_replace(["\n", "\r", "\r\n"], "", $id);

            if (!$id) {
                continue;
            }

            $indexArr[$id] = $key;
        }


        $providerIdArr = [];

        // 删除 名称 行
        $nameLine   = array_shift($lines);

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            foreach ($lineArr as $key => $id) {
                $_id = array_search($key, $indexArr);

                if ($_id !== false) {
                    $id = str_replace(["\n", "\r", "\r\n", '"'], "", $id);
                    $providerIdArr[$_id][] = $id;
                }
            }
        }

        // 遍历数组，进行打包
        $packArr    = [];
        $unPackArr  = [];
        foreach ($providerIdArr as $providerId => $friendIds) {
            $packIds = [];
            foreach ($friendIds as $id) {
                if (empty($id)) {
                    continue;
                }

                $files = glob(FRIEND_DB_FOLDER . $id . " 20*");
                if (empty($files)) {
                    $unPackArr[$providerId][] = $id;
                    continue;
                }

                $packIds = array_merge($packIds, file($files[0]));
            }

            // 去掉尾随的换行符
            foreach ($packIds as $key => $packId) {
                $packIds[$key] = str_replace(["\n", "\r", "\r\n", '"'], "", $packId);
            }

            // 不同账号的好友合并之后进行去重
            $packIds = array_unique($packIds);

            // 对数组进行压缩
            $packIds = array_chunk($packIds, 2000);
            foreach ($packIds as $key => $chunk) {
                $packIds[$key] = "/" . implode("/", $chunk) . "/";
            }

            $packArr[$providerId] = $packIds;
        }

        /*
            将打包的数组转换为 tsv 格式的文件
            packArr 
                12345 => pack1
                         pack2
                         pack3

                23456 => pack1
                         pack2
        */

        // 将导出的ID打包
        $maxColumn  = count($packArr);

        $maxLine    = 0;
        foreach ($packArr as $packIds) {
            if (count($packIds) > $maxLine) {
                $maxLine = count($packIds);
            }
        }

        $outputStr = $nameLine;
        for ($i = 0; $i < $maxLine; $i++) {
            for ($j = 0; $j < $maxColumn; $j++) {
                $providerId = array_search($j, $indexArr);
                $outputStr .= ($packArr[$providerId][$i] ?? "") . "\t";
            }

            $outputStr .= PHP_EOL;
        }

        $outputFileName = FRIEND_OUTPUT_PATH . CURRENT_TIME . ".tsv";
        file_put_contents($outputFileName, $outputStr);

        // 将没有导出好友的ID重新生成对应的文件
        $maxUnPackColumn  = count($unPackArr);
        $maxUnPackLine    = 0;

        foreach ($unPackArr as $unPackIds) {
            if (count($unPackIds) > $maxUnPackLine) {
                $maxUnPackLine = count($unPackIds);
            }
        }

        $_outputStr = $nameLine;
        for ($i = 0; $i < $maxUnPackLine; $i++) {
            for ($j = 0; $j < $maxUnPackColumn; $j++) {
                $providerId = array_search($j, $indexArr);
                $_outputStr .= ($unPackArr[$providerId][$i] ?? "") . "\t";
            }

            $_outputStr .= PHP_EOL;
        }

        $outputFileName = FRIEND_OUTPUT_PATH . CURRENT_TIME . " left id.tsv";
        file_put_contents($outputFileName, $_outputStr);
    }

    // 挑选中文名，中文名需要位于第二位
    public function selectName($file)
    {
        $lines = file($file);

        $chineseIds = [];
        $chineseNames = [];
        $notChineseNames = [];

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id      = $lineArr[0] ?? "";
            $name    = str_replace(["\n", "\r", "\r\n"], "", $lineArr[1] ?? "");

            // 过滤到死账号
            if (str_contains($name, "Facebook")) {
                continue;
            }

            // 先检测是否是汉字
            if (\containsChinese($name)) {
                $chineseIds[]   = $id;
                $chineseNames[] = $line;
                continue;
            } else {
                // 没有汉字的情况下，检查英文中是否有中文拼音
                $preg_1 = "/^(" . implode("|", $this->nameKeywords) . ") /";
                $preg_2 = "/ (" . implode("|", $this->nameKeywords) . ")$/";

                if (preg_match($preg_1, $name) || preg_match($preg_2, $name)) {
                    $chineseIds[]   = $id;
                    $chineseNames[] = $line;
                    continue;
                }

                $notChineseNames[] = $line;
            }
        }

        $currentTime = date("Y-m-d H:i:s");
        $outputFileName = FRIEND_OUTPUT_PATH . $currentTime . " chinese.tsv";
        file_put_contents($outputFileName, implode("", $chineseNames));

        $outputFileName = FRIEND_OUTPUT_PATH . $currentTime . " chinese id.tsv";
        file_put_contents($outputFileName, implode(PHP_EOL, $chineseIds));

        $outputFileName = FRIEND_OUTPUT_PATH . $currentTime . " not chinese.tsv";
        file_put_contents($outputFileName, implode("", $notChineseNames));
    }


    /**
     * 根据指定的ID移除对应的 好友ID 文件
     */
    public function removeIdFiles($file)
    {
        $removeIds = getLine($file);

        $count = 0;
        foreach ($removeIds as $id) {
            $files = glob(FRIEND_DB_FOLDER . $id . " 202*");
            if (empty($files)) {
                continue;
            }

            foreach ($files as $file) {
                unlink($file);
                $count++;
                echo basename($file) . PHP_EOL;
            }
        }

        return $count;
    }

    public function getFriendFilesIds($file)
    {

        $ids = getLine($file);

        $results = [];
        $skipIds = [];
        foreach ($ids as $id) {
            $filePath = FRIEND_FILES_PURE_FOLDER . $id;
            if (!file_exists($filePath)) {
                $skipIds[] = $id;
                continue;
            }

            $results = array_merge($results, getLine($filePath));
        }

        $results = array_unique($results);

        $outputFileName = FRIEND_OUTPUT_PATH . CURRENT_TIME . " friend ids.tsv";
        file_put_contents($outputFileName, implode(PHP_EOL, $results));

        $outputFileName = FRIEND_OUTPUT_PATH . CURRENT_TIME . " skip ids.tsv";
        file_put_contents($outputFileName, implode(PHP_EOL, $skipIds));

        $this->app->info(sprintf("ID共 %d 个, 跳过 %d 个; 好友ID %d 个", count($ids), count($skipIds), count($results)));
    }
}
