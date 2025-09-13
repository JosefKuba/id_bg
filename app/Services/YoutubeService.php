<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class YoutubeService implements ServiceInterface
{
    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 根据关键词搜索视频
    public function search($keywords)
    {
        $apiKeys = getLine(ROOT_PATH . "secrets/apikeys");

        foreach ($keywords as $key => $keyword) {

            if (!str_contains($keyword, " ")) {
                continue;
            } else {
                $keyword = str_replace(" ", "+", $keyword);
            }

            // 随机抽取一个 key
            $apiKey =  $apiKeys[array_rand($apiKeys)];

            // 调用搜索 api, 获取结果
            $searchApi = sprintf(
                "https://www.googleapis.com/youtube/v3/search?part=snippet&q=%s&type=video&maxResults=45&key=%s&regionCode=HU&relevanceLanguage=hu&order=viewCount",
                $keyword,
                $apiKey
            );

            $results = file_get_contents($searchApi);

            // 处理结果
            $results = json_decode($results, true);

            $videos  = $results['items'];

            $videoIds = [];

            $videoData = [];
            foreach ($videos as $video) {
                $videoId        = $video['id']['videoId'];
                $channelId      = $video['snippet']['channelId'];

                // 收集 videoId
                $videoIds[] = $videoId;

                // 收集视频信息
                $videoData[$videoId] = [
                    'videoId'        => $videoId,
                    'publishDate'    => explode("T", $video['snippet']['publishedAt'])[0],
                    'title'          => $video['snippet']['title'],
                    'videoUrl'       => sprintf("https://www.youtube.com/watch?v=%s", $videoId),
                    'videoThumbnail' => $video['snippet']['thumbnails']['high']['url'],

                    'channelTitle'   => $video['snippet']['channelTitle'],
                    'channelUrl'     => sprintf("https://www.youtube.com/channel/%s", $channelId),
                ];
            }

            // 获取视频的详细信息
            $videoApi = sprintf(
                "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&key=%s&id=%s", 
                $apiKey,
                implode(",", $videoIds),
            );

            $results = file_get_contents($videoApi);

            $results = json_decode($results, true);

            $videos = $results['items'];

            foreach ($videos as $video) {
                $videoId = $video['id'];
                
                $videoData[$videoId]['content']     = $video['snippet']['description'] ?? "";
                $videoData[$videoId]['duration']    = $video['contentDetails']['duration'] ?? "";
                $videoData[$videoId]['viewCount']   = $video['statistics']['viewCount'] ?? "";
                $videoData[$videoId]['likeCount']   = $video['statistics']['likeCount'] ?? "";
            }

            // 处理输出结果
            $output = "";
            foreach ($videoData as $item) {
                $output .= implode(
                    "\t", 
                    [
                        $item['videoId'],
                        $item['publishDate'],
                        $item['title'],
                        $item['videoUrl'],
                        $item['videoThumbnail'],
                        $this->iso8601ToSeconds($item['duration']),
                        $item['viewCount'],
                        $item['likeCount'],
                        
                        $item['channelTitle'],
                        $item['channelUrl'],
                        str_replace(["\r", "\n", "\r\n"], "%%%", $item['content']),
                    ]
                ) . PHP_EOL;
            }

            // 保存结果
            $path = YTB_OUTPUT_PATH . CURRENT_TIME . ' result';
            file_put_contents($path, $output, FILE_APPEND);

            $this->app->info(sprintf("进度: %d / %d 关键词 %s 搜索完成", ($key + 1), count($keywords), $keyword));
        }
    }

    private function iso8601ToSeconds($duration) {
        // 验证基本格式
        if (!preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?)?$/', $duration, $matches)) {
            throw new \InvalidArgumentException("无效的ISO 8601持续时间格式: $duration");
        }
        
        // 提取各个时间组件
        $years = isset($matches[1]) ? (int)$matches[1] : 0;
        $months = isset($matches[2]) ? (int)$matches[2] : 0;
        $days = isset($matches[3]) ? (int)$matches[3] : 0;
        $hours = isset($matches[4]) ? (int)$matches[4] : 0;
        $minutes = isset($matches[5]) ? (int)$matches[5] : 0;
        $seconds = isset($matches[6]) ? (float)$matches[6] : 0;
        
        // 转换为总秒数（简化计算：1年=365天，1月=30天）
        $totalSeconds = 0;
        $totalSeconds += $years * 365 * 24 * 3600;  // 年
        $totalSeconds += $months * 30 * 24 * 3600;  // 月
        $totalSeconds += $days * 24 * 3600;         // 天
        $totalSeconds += $hours * 3600;             // 小时
        $totalSeconds += $minutes * 60;             // 分钟
        $totalSeconds += $seconds;                  // 秒
        
        return (int)$totalSeconds;
    }

    public function shorts($files) 
    {
        foreach ( $files as $file ) {

            $html = file_get_contents($file);

            // 创建 DOMDocument 实例
            $doc = new \DOMDocument();

            // 忽略格式错误的 HTML 警告
            libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            libxml_clear_errors();

            // 创建数组存储提取的 Shorts 数据
            $shorts = [];

            // 使用 XPath 查询包含 Shorts 链接的 <a> 标签
            $xpath = new \DOMXPath($doc);
            //$nodes = $xpath->query('//a[contains(@href, "/shorts/")]');

            // 找到所有 shorts 链接
            $links = $xpath->query("//a[contains(@href, '/shorts/')]");

            foreach ($links as $a) {
                $href = $a->getAttribute("href");
                if (strpos($href, "/shorts/") === false) continue;

                // 视频 ID
                preg_match("#/shorts/([^/?&]+)#", $href, $m);
                if (!$m) continue;
                $videoId = $m[1];

                // 链接
                $link = strpos($href, "http") === 0 ? $href : "https://www.youtube.com" . $href;

                // 标题
                $title = $a->getAttribute("title");
                if (!$title) {
                    $span = $a->getElementsByTagName("span")->item(0);
                    if ($span) $title = trim($span->textContent);
                }

                // 观看量（查找相邻的 div）
                $views = "";
                $nextDiv = $a->parentNode->parentNode->getElementsByTagName("div");
                foreach ($nextDiv as $d) {
                    if (strpos($d->getAttribute("class"), "shortsLockupViewModelHostOutsideMetadataSubhead") !== false) {
                        $views = trim($d->textContent);
                        break;
                    }
                }

                $views = str_replace("次观看", "", $views);
                if (str_contains($views, "万")) {
                    $views = str_replace("万", "", $views) * 10000;
                }

                $thumbnail = "https://i.ytimg.com/vi/" . $videoId . "/oar2.jpg";

                $shorts[$videoId] = $title . "\t" . $link . "\t" . $thumbnail . "\t" . $views;
            }

            $outputpath = YTB_OUTPUT_PATH . CURRENT_TIME . " shorts.tsv";
            file_put_contents($outputpath, implode(PHP_EOL, $shorts), FILE_APPEND);
        }
    }
}
