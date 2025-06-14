<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class LinkService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 处理链接
    public function pure($filePath)
    {
        $links = getLine($filePath);

        $pageLinks = $pureLinks = $otherLinks = [];

        foreach ($links as $link) {

            if (!str_contains($link, 'https://www.facebook.com')) {
                continue;
            }

            // 替换 https://www.facebook.com/100046331054273
            // 替换 https://www.facebook.com/61550491010352

            $link = str_replace("https://facebook", "https://www.facebook", $link);
            $link = str_replace("com/1000", "com/profile.php?id=1000", $link);
            $link = str_replace("com/615", "com/profile.php?id=615", $link);

            // 去掉 &__cft__[0]
            $link = preg_replace("/(\?|&)__cft__.*$/", '', $link);

            // 去掉 ?__tn__
            $link = preg_replace("/(\?|&)__(tn|cft)__.*$/", '', $link);

            // 去掉评论链接
            $link = preg_replace("/(\?|&)comment_id.*$/", '', $link);

            $link = preg_replace('/\/$/', '', $link);

            // 去掉帖文链接
            // $link = preg_replace("/posts\/\d+$/", "", $link);

            // 处理小组链接中带有的后缀
            // https://www.facebook.com/groups/183153232482568/?hoisted_section_header_type=recently_seen&multi_permalinks=1699532834177926
            // https://www.facebook.com/groups/hasznltauto/buy_sell_discussion

            // 处理小组链接
            if (str_contains($link, "groups")) {
                preg_match("/https:\/\/www.facebook\.com\/groups\/[^?\/]+/", $link, $matches);
                $link = $matches[0];
            }

            if (str_contains($link, '/pages/')) {
                $pageLinks[] = $link;
            } else if (
                str_contains($link, '/stories/') ||
                str_contains($link, 'story_fbid') ||
                str_contains($link, 'com/%') ||
                str_contains($link, '/media/') ||
                str_contains($link, 'com/@')
            ) {
                $otherLinks[] = $link;
            } else {
                $pureLinks[] = $link;
            }
        }

        if ($pageLinks) {
            $pagePath = LINK_OUTPUT_PATH . CURRENT_TIME . " page";
            file_put_contents($pagePath, implode(PHP_EOL, $pageLinks));
        }

        if ($pureLinks) {
            $purePath = LINK_OUTPUT_PATH . CURRENT_TIME . " pure";
            file_put_contents($purePath, implode(PHP_EOL, $pureLinks));
        }

        if($otherLinks) {
            $otherPath = LINK_OUTPUT_PATH . CURRENT_TIME . " other";
            file_put_contents($otherPath, implode(PHP_EOL, $otherLinks));
        } 

    }
}
