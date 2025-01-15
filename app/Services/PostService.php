<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class PostService implements ServiceInterface
{

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 处理贴文的 点赞 和 时间
    public function classify($file)
    {

        $lines = getLine($file);

        $badPosts = [];
        $earlyPosts = [];
        $zeroLikePosts = [];
        $pageIdBadPosts = [];
        $normalPosts = [];

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);

            $date = $lineArr[2];
            $likeCount = $lineArr[3];
            $pageId = $lineArr[6];

            // 过滤掉有问题的贴文
            if (!is_numeric($likeCount)) {
                $badPosts[] = $line;
                continue;
            }

            // 过滤掉专页ID有问题的贴文
            if (!is_numeric($pageId)) {
                $pageIdBadPosts[] = $line;
                continue;
            }

            // 过滤掉0点赞的贴文
            if ($likeCount == 0 ||  $likeCount == 1) {
                $zeroLikePosts[] = $line;
                continue;
            }

            // 区分时间早的贴文
            $year = substr($date, 0, 4);
            if ($year < 2022) {
                $earlyPosts[] = $line;
            } else {
                $normalPosts[] = $line;
            }
        }

        if (!empty($badPosts)) {
            $path = POST_OUTPUT_PATH  . CURRENT_TIME . " bad posts";
            file_put_contents($path, implode(PHP_EOL, $badPosts));
        }

        if (!empty($pageIdBadPosts)) {
            $path = POST_OUTPUT_PATH  . CURRENT_TIME . " page_id bad posts";
            file_put_contents($path, implode(PHP_EOL, $pageIdBadPosts));
        }

        if (!empty($zeroLikePosts)) {
            $path = POST_OUTPUT_PATH  . CURRENT_TIME . " zero like posts";
            file_put_contents($path, implode(PHP_EOL, $zeroLikePosts));
        }

        if (!empty($earlyPosts)) {
            $path = POST_OUTPUT_PATH  . CURRENT_TIME . " early posts";
            file_put_contents($path, implode(PHP_EOL, $earlyPosts));
        }

        if (!empty($normalPosts)) {
            $path = POST_OUTPUT_PATH  . CURRENT_TIME . " normal posts";
            file_put_contents($path, implode(PHP_EOL, $normalPosts));
        }

        $this->app->info(sprintf("贴文共计 %d 个", count($lines)));
        $this->app->info(sprintf("坏贴 %d 个", count($badPosts)));
        $this->app->info(sprintf("专页ID有问题贴 %d 个", count($pageIdBadPosts)));
        $this->app->info(sprintf("0点赞贴 %d 个", count($zeroLikePosts)));
        $this->app->info(sprintf("时间早贴 %d 个", count($earlyPosts)));
        $this->app->info(sprintf("正常贴文 %d 个", count($normalPosts)));
    }
}
