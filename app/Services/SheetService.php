<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class SheetService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    // 汇总各类表格链接的仪表盘
    private $indexSheetUrl = "https://docs.google.com/spreadsheets/d/16Ry0Fca7gNVLawRohgr1Tbp1kzzXtrwxBJo3DvDkgac/edit?gid=1151949234#gid=1151949234";

    /**
     * 需要带有参数
     *  - type
     *      - page_post                 下载一览表专页帖文信息
     *      - group_post                下载一览表小组帖文信息
     *      - post_auto_fill            下载帖文填表工具
     *      - post_auto_fill_unique     帖文填表工具去重
     *      - signal_sheet              下载单个分页中所有的数据
     *      - upload                    上传数据
     *  - url
     *  - sheetName             下载单个分页中所有的数据 时，需要指定 分页名称
     */
    private $indexSheetApi = "https://script.google.com/macros/s/AKfycbyNvRuHDaO8NQhj3iAjCFnMfoBJHE46Ks7VOV0PwEtl2BJxUgVew_0QwfflcnGFhcgC/exec";

    private $token = "1F2eFbVgYX.jS3LhM53OL";

    private $postFillFormSheetName = "发帖登记表";

    private $chatbotSheetName = "chatbot表";

    private $postDetailSheetName = "【一览表】帖文";



    // 小组数据统计基础表格, 上传数据用
    private $groupBaseDataSheetUrl = "https://docs.google.com/spreadsheets/d/1gHGFB0cnyuhsFWKpRX-npjFpizpy8vONeeSLJyB8_-o/edit?gid=21561019#gid=21561019";

    // 存放小组发帖量的基础数据
    private $groupPostSheetName = "小组发帖量";

    // 存放小组引流量的基础数据
    private $groupChatbotSheetName = "小组引流量";


    // 收集本次下载的发帖填表工具的路径
    private $postFillFormPaths = [];

    // 收集本次下载的chatbot的路径
    private $chatbotPahts = [];


    // 下载下来的配置文件的路径
    private $namePath =  "";

    public function load(App $app): void
    {
        $this->app = $app;
    }


    // 发帖表内容去重
    public function postFillFormTableUnique()
    {
        $startTime = time();

        $content = $this->fetchWithRetry($this->getApiUrl('signal_sheet', $this->indexSheetUrl, $this->postFillFormSheetName));

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取发帖登记表 链接 失败");
            die;
        }

        $endTime = time();

        $path = SHEET_INPUT_PATH . CURRENT_TIME . " 发帖表.tsv";

        file_put_contents($path, $content);
        
        $this->app->info(sprintf("获取发帖表链接完成; 用时 %s 秒", $endTime - $startTime));

        // 2. 下载每一个链接
        $lines = getLine($path);

        foreach ($lines as $key => $line) {
            
            $lineArr = explode("\t", $line);

            $name   = $lineArr[0] ?? ""; 
            $url    = $lineArr[1] ?? "";

            if (!str_contains($url, "https")) {
                $this->app->info(sprintf("%d / %d; 链接不符合要求 跳过", ($key+1), count($lines)));
                continue;
            }

            $startTime = time();

            $_url = $this->getApiUrl('post_auto_fill_unique', $url);
            $content = $this->fetchWithRetry($_url);

            if ($content === false) {
                $this->app->error(sprintf("获取发帖登记表: %s 内容失败", $name));
                continue;
            }

            $endTime = time();

            $this->app->info(sprintf("%d / %d; %s 数据处理完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));
        
        }
    }

    // 下载发帖表列表
    public function downloadPostFillFormTableList()
    {
        // 1. 获取发帖登记表的链接
        $startTime = time();

        $content = $this->fetchWithRetry($this->getApiUrl('signal_sheet', $this->indexSheetUrl, $this->postFillFormSheetName));

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取发帖登记表 链接 失败");
            die;
        }

        $endTime = time();

        $path = SHEET_INPUT_PATH . CURRENT_TIME . " 发帖表.tsv";

        file_put_contents($path, $content);
        
        $this->app->info(sprintf("获取发帖表链接完成; 用时 %s 秒", $endTime - $startTime));

        return $path;
    }

    // 下载每一个发帖登记表
    public function downloadPostFillFormTable($path)
    {
        // 2. 下载每一个链接
        $lines = getLine($path);

        foreach ($lines as $key => $line) {
            
            $lineArr = explode("\t", $line);

            $name   = $lineArr[0] ?? ""; 
            $url    = $lineArr[1] ?? "";

            if (str_contains($url, "https")) {
                $startTime = time();

                $_url = $this->getApiUrl('post_auto_fill', $url);
                $content = $this->fetchWithRetry($_url);

                if ($content === false) {
                    $this->app->error(sprintf("获取发帖登记表: %s 内容失败", $name));
                    continue;
                }

                $path = SHEET_INPUT_PATH . CURRENT_TIME . " " . $name . ".tsv";
                file_put_contents($path, $content);

                $this->postFillFormPaths[] = $path;

                $endTime = time();

                $this->app->info(sprintf("%d / %d; %s 下载完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));
            }
        }
    }

    // 下载chatbot列表
    public function downloadChatbotTableList()
    {
        // 1. 获取发帖登记表的链接
        $startTime = time();

        $_url = $this->getApiUrl('signal_sheet', $this->indexSheetUrl, $this->chatbotSheetName);
        $content = $this->fetchWithRetry($_url);

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取发帖登记表 链接 失败");
            die;
        }

        $endTime = time();

        $path = SHEET_INPUT_PATH . CURRENT_TIME . " 引流表.tsv";

        file_put_contents($path, $content);
        
        $this->app->info(sprintf("引流表链接完成; 用时 %s 秒", $endTime - $startTime));

        return $path;
    }

    // 下载每一个 chatbot 表
    public function downloadChatbotTable($path)
    {
        // 2. 下载每一个链接
        $lines = getLine($path);

        foreach ($lines as $key => $line) {
            
            $lineArr = explode("\t", $line);

            $name   = $lineArr[1] ?? ""; 
            $url    = $lineArr[2] ?? "";

            if (str_contains($url, "https")) {
                $startTime = time();

                $_url = $this->getApiUrl('signal_sheet', $url, "订阅");
                $content = $this->fetchWithRetry($_url);

                if ($content === false) {
                    $this->app->error(sprintf("获取引流表: %s 内容失败", $name));
                    continue;
                }

                $path = SHEET_INPUT_PATH . CURRENT_TIME . " " . $name . ".tsv";
                file_put_contents($path, $content);

                $this->chatbotPahts[] = $path;

                $endTime = time();

                $this->app->info(sprintf("%d / %d; %s 下载完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));
            }
        }
    }


    // 统计每一个小组的发帖量
    public function statisticGroupsPost($files = [])
    {
        // 1. 统计每一个小组的发帖量
        $postData = [];
        $groupIds = [];

        if (empty($this->postFillFormPaths)) {
            $this->postFillFormPaths = $files;
        }

        foreach ($this->postFillFormPaths as $file) {

            $lines = getLine($file);
            
            foreach ($lines as $line) {
                
                if (!str_contains($line, "https")) {
                    continue;
                }
                
                $lineArr = explode("\t", $line);

                $dateStr    = $lineArr[1]; // tip: 这里不能用0
                
                // 计算发帖日期距离现在的天数
                $daysFromNow = $this->daysSinceJsDate($dateStr);
                
                // 只保留半个月的数据
                if ($daysFromNow > 15) {
                    continue;
                }

                // 收集小组ID
                $groupId = $lineArr[8];
                $groupIds[$groupId] = 1;

                // 按照天来区分，保存为二维数组
                $postData[$groupId][$daysFromNow] = ($postData[$groupId][$daysFromNow] ?? 0) + 1;
            }
        }

        $tableTitle = [
            "小组ID",
            date('Y-m-d'),
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d', strtotime('-2 day')),
            date('Y-m-d', strtotime('-3 day')),
            date('Y-m-d', strtotime('-4 day')),
            date('Y-m-d', strtotime('-5 day')),
            date('Y-m-d', strtotime('-6 day')),
            date('Y-m-d', strtotime('-7 day')),
            date('Y-m-d', strtotime('-8 day')),
            date('Y-m-d', strtotime('-9 day')),
            date('Y-m-d', strtotime('-10 day')),
            date('Y-m-d', strtotime('-11 day')),
            date('Y-m-d', strtotime('-12 day')),
            date('Y-m-d', strtotime('-13 day')),
            date('Y-m-d', strtotime('-14 day')),
        ];
        $results = implode("\t", $tableTitle) . "\n";

        foreach ($postData as $groupId => $items) {
            $results .= $groupId;
            for ($i = 0;  $i < 15; $i++ ) {
                if (!array_key_exists($i, $items)) {
                    $postData[$groupId][$i] = $items[$i] = 0;
                }
                $results .= "\t" . ($items[$i] ?? 0);
            }
            $results .= "\n"; 
        }

        // 本地保存数据
        $path = SHEET_OUTPUT_PATH . CURRENT_TIME . " group results.tsv";
        file_put_contents($path, $results);

        // 上传数据
        $uploadData[0] = $tableTitle;
        $i = 1;
        foreach ($postData as $groupId => $items) {
            if (empty($groupId)) {
                continue;
            }

            $uploadData[$i] = [
                $groupId, 
                $items[0],
                $items[1],
                $items[2],
                $items[3],
                $items[4],
                $items[5],
                $items[6],
                $items[7],
                $items[8],
                $items[9],
                $items[10],
                $items[11],
                $items[12],
                $items[13],
                $items[14],
            ];

            $i++;
        }

        $this->uploadGoogleSheet($this->groupBaseDataSheetUrl, $this->groupPostSheetName, $uploadData);
    }

    // 统计15天小组的引流总数
    public function statisticChatbot($files = [])
    {
        if (empty($this->chatbotPahts)) {
            $this->chatbotPahts = $files;
        }

        $chatbotData = [];

        foreach ($this->chatbotPahts as $file) {
            $lines = getLine($file);

            foreach ($lines as $line) {
                // 过滤掉不是小组引流的行
                if (!str_contains($line, "groups")) {
                    continue;
                }

                $lineArr = explode("\t", $line);

                $dateStr = $lineArr[12] ?? "";
                if (!$dateStr) {
                    continue;
                }

                $daysFromNow = $this->daysSinceJsDate($dateStr);
                
                // 只保留半个月的数据
                if ($daysFromNow > 15) {
                    continue;
                }

                $link = $lineArr[9];

                $isMatch = preg_match('#/groups/(\d+)#', $link, $matches);
                if (!$isMatch) {
                    continue;
                }

                $groupId = $matches[1];
                
                $chatbotData[$groupId] = ($chatbotData[$groupId] ?? 0) + 1;
            }
        }

        $tableTitle = [
            "小组ID",
            "引流数"
        ];
        $result = implode("\t", $tableTitle) . "\n";

        foreach ($chatbotData as $groupId => $count) {
            $result .= implode("\t", [$groupId, $count]) . "\n";
        }

        $path = SHEET_OUTPUT_PATH . CURRENT_TIME . " chatbot results.tsv";
        file_put_contents($path, $result);

        // 上传数据
        $uploadData[0] = $tableTitle;
        $i = 1;
        foreach ($chatbotData as $groupId => $count) {
            $uploadData[$i] = [$groupId, $count];
            $i++;
        }

        $this->uploadGoogleSheet($this->groupBaseDataSheetUrl, $this->groupChatbotSheetName, $uploadData);
    }

    // 统计每个帖文的引流量
    public function statisticPostEffect()
    {
        $files = glob(SHEET_INPUT_PATH . "*");

        $results = [];
        $collectPosts = [];

        foreach ($files as $file) {
            $lines = getLine($file);

            foreach ($lines as $line) {
                $lineArr = explode("\t", $line);

                $date = $lineArr[5] ?? "";
                $link = trim($lineArr[9] ?? "", "/");

                if (empty($date) || empty($link)) {
                    continue;
                }

                $dateToNow = $this->daysSinceJsDate($date);
                
                // todo 每次使用需要修改
                if ( $dateToNow < 1 || $dateToNow >= 23 ) {
                    continue;
                }

                $collectPosts[] = $line;

                $delimiter = str_contains($link, "_") ? "_" : "/";

                $_arr = explode($delimiter, $link);
                $id = array_pop($_arr);

                if (array_key_exists($id, $results)) {
                    $results[$id]['count']++;
                } else {
                    $results[$id] = [
                        'count' => 1,
                        'link'  => $link,
                    ];
                }
            }
        }

        $output = "";
        foreach ($results as $id => $item) {
            if ($item['count'] < 10) {
                continue;
            }

            $output .= $id . "\t" . $item['count']  . "\t" . $item['link'] . "\n";
        }

        $path = SHEET_OUTPUT_PATH . CURRENT_TIME . " result.tsv";
        file_put_contents($path, $output);

        $path = SHEET_OUTPUT_PATH . CURRENT_TIME . " posts.tsv";
        file_put_contents($path, implode(PHP_EOL, $collectPosts));

        $this->app->info("引流帖文统计完成");
    }

    // 备份 chatbot 表格
    public function backupChatbotTable()
    {
        // 1. 获取发帖登记表的链接
        $startTime = time();

        $_url = $this->getApiUrl('signal_sheet', $this->indexSheetUrl, $this->chatbotSheetName);
        $content = $this->fetchWithRetry($_url);

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取发帖登记表 链接 失败");
            die;
        }

        $endTime = time();

        $path = SHEET_INPUT_PATH . CURRENT_TIME . " 引流表.tsv";

        file_put_contents($path, $content);
        
        $this->app->info(sprintf("引流表链接完成; 用时 %s 秒", $endTime - $startTime));

        // 2. 备份每一个链接
        $lines = getLine($path);

        foreach ($lines as $key => $line) {
            
            $lineArr = explode("\t", $line);

            $name   = $lineArr[1] ?? ""; 
            $url    = $lineArr[2] ?? "";

            if (!str_contains($url, "https")) {
                continue;
            }

            $startTime = time();

            $_url = $this->getApiUrl('backup_chatbot', $url);
            $content = $this->fetchWithRetry($_url);

            if ($content === false) {
                $this->app->error(sprintf("获取引流表: %s 内容失败", $name));
                continue;
            }

            $endTime = time();

            $this->app->info(sprintf("%d / %d; %s 处理完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));

        }

    }


    // 将数据写入 Google Sheet
    public function uploadGoogleSheet($url, $sheetName, $data)
    {
        $id = $this->getIdFromeSheetUrl($url);

        $_data = [
            "type" => "upload",
            "sheetName" => $sheetName,
            "url" => $id,
            "data" => json_encode($data)
        ];

        // 1. 构建 POST 数据为 x-www-form-urlencoded 格式
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'method'  => 'POST',
                'content' => http_build_query($_data),
                'timeout' => 60,
                'token'   => $this->token,
            ]
        ];

        $context = stream_context_create($options);

        // 2. 发出 POST 请求
        $response = file_get_contents($this->indexSheetApi, false, $context);

        $this->app->info("上传信息: " . $response);
    }


    // 清理表格
    public function cleanTable()
    {
        // 1. 获取发帖登记表的链接
        $startTime = time();

        $_url = $this->getApiUrl('signal_sheet', $this->indexSheetUrl, "临时");
        $content = $this->fetchWithRetry($_url);

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取链接失败");
            die;
        }

        $endTime = time();

        $path = SHEET_INPUT_PATH . CURRENT_TIME . " 清理链接.tsv";

        file_put_contents($path, $content);
        
        $this->app->info(sprintf("清理链接下载完成; 用时 %s 秒", $endTime - $startTime));

        // 2. 清理每一个链接
        $lines = getLine($path);

        foreach ($lines as $key => $line) {
            
            $lineArr = explode("\t", $line);

            $name   = $lineArr[0] ?? "";
            $url    = $lineArr[1] ?? "";

            if (str_contains($url, "https")) {
                $startTime = time();

                $_url = $this->getApiUrl('clean', $url, "帖文汇总", 2);
                
                $content = $this->fetchWithRetry($_url);

                if ($content === false) {
                    $this->app->error(sprintf("清理数据表: %s 内容失败", $name));
                    continue;
                }

                $endTime = time();

                $this->app->info(sprintf("%d / %d; %s 清理完成; 用时 %d 秒: %s", ($key+1), count($lines), $name, ($endTime - $startTime), $content));
            }
        }
    }

    // 下载帖文一览表中的专页帖文和小组帖文
    public function downloadPost() 
    {
        // todo
        // 1. 获取发帖登记表的链接
        $startTime = time();

        $_url = $this->getApiUrl('signal_sheet', $this->indexSheetUrl, "【一览表】帖文");
        $content = $this->fetchWithRetry($_url);

        // 请求失败，比如 404、超时、DNS 错误等
        if ($content === false) {
            $this->app->error("获取链接失败");
            die;
        }

        $endTime = time();

        $namePath = SHEET_INPUT_PATH . CURRENT_TIME . " 帖文链接.tsv";

        $this->namePath = $namePath;

        file_put_contents($namePath, $content);
        
        $this->app->info(sprintf("帖文链接下载完成; 用时 %s 秒", $endTime - $startTime));

        // 2. 下载每一个链接
        $lines = getLine($namePath);

        foreach ($lines as $key => $line) {
            $lineArr = explode("\t", $line);
            $name = $lineArr[0];
            $pageUrl = $lineArr[1];
            $groupUrl = $lineArr[2];

            // 下载专页帖文
            if (str_contains($pageUrl, "https")) {
                $startTime = time();

                $_url = $this->getApiUrl('page_post', $pageUrl);
                
                $content = $this->fetchWithRetry($_url);

                if ($content === false) {
                    $this->app->error(sprintf("下载专页帖文: %s 内容失败", $name));
                    continue;
                }

                $path = SHEET_INPUT_PATH . CURRENT_TIME . " " . $name . " 专页帖文.tsv";
                file_put_contents($path, $content);

                $endTime = time();

                $this->app->info(sprintf("%d / %d; %s 专页帖文下载完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));
            }

            // 下载小组帖文
            if (str_contains($groupUrl, "https")) {
                $startTime = time();

                $_url = $this->getApiUrl('group_post', $groupUrl);
                
                $content = $this->fetchWithRetry($_url);

                if ($content === false) {
                    $this->app->error(sprintf("下载小组帖文: %s 内容失败", $name));
                    continue;
                }

                $path = SHEET_INPUT_PATH . CURRENT_TIME  . " " . $name . " 小组帖文.tsv";
                file_put_contents($path, $content);

                $endTime = time();

                $this->app->info(sprintf("%d / %d; %s 小组帖文下载完成; 用时 %d 秒", ($key+1), count($lines), $name, ($endTime - $startTime)));
            }
        }
    }

    // 重新尝试获取失败的链接
    private function fetchWithRetry($url, $context = [], $maxRetries = 3, $waitTime = 2) {

        if (empty($context)) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 300  // 超时时间（秒）
                ]
            ]);
        }

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $content = file_get_contents($url, false, $context);
            
            if ($content !== false) {
                return $content;
            }
            
            if ($attempt < $maxRetries) {
                sleep($waitTime);
            }
        }
        
        return false;
    }


    // 获取访问api的完整链接
    public function getApiUrl($type, $url, $sheetName = "", $startRow=0)
    {
        $id = $this->getIdFromeSheetUrl($url);
        return $this->indexSheetApi . sprintf("?type=%s&url=%s&sheetName=%s&token=%s&startRow=%s", $type, $id, $sheetName, $this->token, $startRow);
    }

    // 从 google 链接中提取 ID
    private function getIdFromeSheetUrl($url) 
    {
        $isMatch = preg_match("/[-\w]{25,}/", $url, $matches);

        if (!$isMatch) {
            $this->app->error("未匹配到表格ID");
        }

        return $matches[0] ?? "";
    }

    // 计算时间与当前时间的差距
    private function daysSinceJsDate(string $jsDateStr)
    {
        // 1. 清除括号中的本地时区描述
        $cleaned = preg_replace('/\s*\(.*\)$/', '', $jsDateStr);

        // 2. 替换 GMT+0000 为 +0000（偏移量）
        $cleaned = str_replace('GMT', '', $cleaned);

        // 3. 尝试解析为 DateTime 对象
        $dt = \DateTime::createFromFormat('D M d Y H:i:s O', $cleaned);
        if (!$dt) {
            return null;  // 解析失败
        }

        // 4. 获取当前日期（不含时分秒）
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $today->setTime(0, 0, 0);

        // 5. 同样重置历史时间的时分秒（只比较天）
        $dt->setTime(0, 0, 0);

        // 6. 计算差值
        $diff = $today->diff($dt);

        return (int)$diff->format('%a'); // 正负天数
    }

    // 


}
