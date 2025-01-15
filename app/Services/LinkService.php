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

            // 去掉 &__cft__[0]
            $link = preg_replace("/(\?|&)__cft__.*$/", '', $link);

            // 去掉 ?__tn__
            $link = preg_replace("/(\?|&)__(tn|cft)__.*$/", '', $link);


            // 转换小组链接
            if (preg_match('/groups\/\d+\/user/', $link)) {
                $link = preg_replace("/groups\/\d+\/user\//", '', $link);
            }

            // 去掉评论链接
            $link = preg_replace("/(\?|&)comment_id.*$/", '', $link);

            $link = preg_replace('/\/$/', '', $link);

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

        $pagePath = LINK_OUTPUT_PATH . CURRENT_TIME . " page";
        file_put_contents($pagePath, implode(PHP_EOL, $pageLinks));

        $purePath = LINK_OUTPUT_PATH . CURRENT_TIME . " pure";
        file_put_contents($purePath, implode(PHP_EOL, $pureLinks));

        $otherPath = LINK_OUTPUT_PATH . CURRENT_TIME . " other";
        file_put_contents($otherPath, implode(PHP_EOL, $otherLinks));
    }
}
