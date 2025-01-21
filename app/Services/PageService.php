<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class PageService implements ServiceInterface
{

    use Trait\SelectTrait;

    private $app;

    private $redisClient;

    private $db_file;

    // 要收集的类型关键词
    private $includeTypeKeywords = [
        "宗教",
        "教会",
        "基督",
        "教堂",
        "会"
    ];

    private $keywordsPreg = "/神学|神學|耶稣|耶穌|诗篇|教区|复临|复活节|禱告|祷告|弥撒|福音|宣教|长老会|国教|兄弟会|圣保罗|圣约翰|圣彼得|圣公会|基督|圣经|信仰|十字架|赞美|敬拜|救世|教派|祈祷|教堂|阿门|聚会|教会|使徒|先知|诫命|十诫|救赎|浸信会|宗教|宗派|牧师|事工|牧区|詩篇|教區|復臨|復活節|彌撒|福音|宣教|長老會|國教|兄弟會|聖保羅|聖約翰|聖彼得|聖公會|基督|聖經|信仰|十字架|讚美|敬拜|救世|教派|祈禱|教會|阿門|聚會|天主|教會|使徒|先知|誡命|十誡|救贖|浸信會|宗派|牧師|牧區|信望爱|传道|荒漠甘泉|天恩|崇拜|恩典|敎会|礼拜|宣道|恩泉|金句|圣洁|团契|信义会|蒙恩|信望愛|傳道|荒漠甘泉|天恩|崇拜|恩典|敎會|禮拜|宣道|恩泉|金句|聖潔|團契|信義会|蒙恩|布道|佈道|天国|天國|修女|修女|路德|衛理|以馬內利|救恩|召会|麥子/";

    public function load(App $app): void
    {
        $this->app = $app;
    }

    public function init()
    {
        $this->redisClient  = $this->app->redis->getPageClient();
        $this->db_file      = PAGE_DB_FILE;
    }

    // 处理合并之后的点赞专页，tsv 文件
    public function handleLikePage()
    {
        /*
        整理导出点赞专页的步骤
            0. 替换链接，把链接替换为标准链接
                https://facebook  替换为 https://www.facebook 
                com/10 替换为 com/profile.php?id=10 
                com/61 替换为 com/profile.php?id=61 

            2. 再根据链接排重
            3. 删除 D列 不需要的派别的专页和 打❌的专页
            4. 剩余的专页，根据正则匹配教派
                没有匹配出来的专页删除
            7. 检测语言
            8. 保留 中文 和 英文 两个语言

            9. 中文的再根据 D列筛选，把天主教的专页放在 “天主教专页” 分页
            10. 把中文和英文的分开，英文的放在 “待排查（英文）” 分页
            11. 其余的专页放在 “待排查（中文）” 分页，并和之前的专页去重
        */

        $this->init();

        $files = glob(PAGE_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $pages = file_get_contents($files[0]);

        // 0. 替换链接
        $pages = str_replace("https://facebook", "https://www.facebook", $pages);
        $pages = str_replace("com/1000", "com/profile.php?id=1000", $pages);
        $pages = str_replace("com/615", "com/profile.php?id=615", $pages);

        // 1. 自身排重
        $pages = explode(PHP_EOL, $pages);

        $totalPagesCount = count($pages);

        $pages = array_map(function ($page) {
            return trim($page, "\t");
        }, $pages);
        $pages = array_unique($pages);

        $uniquePagesCount = count($pages);

        // 需要排查教派的专页
        $checkPages = [];

        // 天主教专页
        $catholicPages = [];


        foreach ($pages as $page) {

            // page
            $pageArr = explode("\t", $page);

            $title  = $pageArr[0] ?? "";
            $id     = trim($pageArr[1] ?? "", "'");
            $link   = $pageArr[2] ?? "";
            $sect   = $pageArr[3] ?? "";

            // 过滤掉不是专页数据的行
            if (!preg_match("/^\d+$/", $id)) {
                continue;
            }

            // 过滤掉小闪电提示的派别
            $sect = str_replace(["\r", "\n", "\r\n"], "", $sect);
            if (in_array($sect, $this->excludeThunderType)) {
                continue;
            }

            // 过滤掉不传到教派
            $excludes = array_merge($this->excludeArea, $this->excludeSect);
            foreach ($excludes as $exclude) {
                if (str_contains($title, $exclude)) {
                    continue 2;
                }
            }

            if (!empty($sect)) {
                // 收集天主教专页
                // if (str_contains($sect, "天主教")) {
                //     $catholicPages[] = "\t\t" . $page;
                //     continue;
                // }

                // 收集基督教专页
                $checkPages[] = $page;
            } else {
                // 检测教派，只收集检查出和宗派相关的专页
                if (preg_match($this->keywordsPreg, $title)) {
                    $checkPages[] = $page;
                }
            }
        }

        // 宗派专页
        $regationPagesCount = count($checkPages);

        // 和总库排重
        $newPages = [];
        // foreach ($checkPages as $key => $page) {
        //     $pageArr = explode("\t", $page);
        //     $link   = $pageArr[2] ?? "";

        //     if ($this->redisClient->exists($link)) {
        //         unset($checkPages[$key]);
        //     } else {
        //         $newPages[] = $link;
        //     }
        // }

        // 重建索引
        $checkPages = array_values($checkPages);

        // 再检测语言，把中文和英文分开
        $zhPages = [];
        $enPages = [];

        $loactionPages = [];

        // 马来西亚语 不检测语言
        // $zhPages = $checkPages;
        $languageClient = new \LanguageDetector\LanguageDetector();
        foreach ($checkPages as $key => $page) {

            // 检查是否是 pages 专页
            if (str_contains($page, "com/pages/")) {
                $loactionPages[] = "\t\t" . $page;
                continue;
            }

            // 对于马来西亚的专页，不区分中文和英文
            // $zhPages[] = "\t\t" . $page;
            // echo 'lang - ' . $key . PHP_EOL;

            $pageArr = explode("\t", $page);
            $title  = $pageArr[0] ?? "";
            // $language = $languageClient->evaluate($title)->getLanguage();

            if (\containsChinese($title)) {
                $zhPages[] = "\t\t" . $page;
            } else {
                $enPages[] = "\t\t" . $page;
            }
        }

        // 新的专页加入总库
        // $this->addPageIntoTotal($newPages);

        // 保存结果
        if ($catholicPages) {
            $catholicPath = PAGE_OUTPUT_PATH . "likes/catholic/" . CURRENT_TIME . " catholic.tsv";
            file_put_contents($catholicPath, implode(PHP_EOL, $catholicPages));
        }

        if ($zhPages) {
            $zhPath = PAGE_OUTPUT_PATH . "likes/zh/" . CURRENT_TIME . " zh.tsv";
            file_put_contents($zhPath, implode(PHP_EOL, $zhPages));
        }

        if ($enPages) {
            $enPath = PAGE_OUTPUT_PATH . "likes/en/" . CURRENT_TIME . " en.tsv";
            file_put_contents($enPath, implode(PHP_EOL, $enPages));
        }

        if ($loactionPages) {
            $locationPath = PAGE_OUTPUT_PATH . "likes/location/" . CURRENT_TIME . " location.tsv";
            file_put_contents($locationPath, implode(PHP_EOL, $loactionPages));
        }


        // 打印结果
        // 1. 统计 合并前多少个。自身去重后多少个，和总库去重后多少个，中文专业多少个，地区专业多少个
        $percent = number_format($uniquePagesCount / $totalPagesCount * 100, "1") . "%";
        $this->app->info(sprintf("专页共计 %d 个，自身去重后 %d 个，不重复比例 %s", $totalPagesCount, $uniquePagesCount, $percent));

        // 宗派专页
        $this->app->info(sprintf("宗派专页 %d 个", $regationPagesCount));

        $percent = number_format(count($checkPages) / $regationPagesCount * 100, "1") . "%";
        $this->app->info(sprintf("和总库去重后 %d 个，不重复比例 %s", count($checkPages), $percent));

        $this->app->info(sprintf("天主教专页 %d 个", count($catholicPages)));
        $this->app->info(sprintf("英文专页 %d 个", count($enPages)));
        $this->app->info(sprintf("地区专页 %d 个", count($loactionPages)));

        $percent = number_format(count($zhPages) / $uniquePagesCount * 100, "1") . "%";
        $this->app->info(sprintf("中文专页 %d 个，剩存比例 %s", count($zhPages), $percent));
    }

    // 处理合并小闪电之后的数据文件
    public function handleSearchPage($type = "")
    {
        /*
            1. 删除掉 小闪电 ✖️ 的专页
            2. 挑选出来 pages 链接
            3. 挑选出来粉丝量少于 100 的专页
            4. 挑选出来 不传派别 的专页 + 澳门 地区

            5. 剩下的专页分为三类[先排序]
                1. 按照专页类别划分 是基督教的专页【一类专页】
                    1. 宗教组织
                    2. 教会
                    3. 教堂
                    4. 社团 + 有小闪电提示的专页
                2. 按照小闪电提示划分是基督教的专页【二类专页】
                    1. 所有有小闪电提示的专页
                3. 剩下的专页【暂缓】
                    1. 没有小闪电提示的专页
        */

        $files = glob(PAGE_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $pages = file_get_contents($files[0]);
        $pages = explode(PHP_EOL, $pages);

        $totalCount = count($pages);

        // 先进行自身排重
        $unique_ids = [];
        foreach ($pages as $key => $page) {
            $pageItems = explode("\t", $page);
            $id = trim($pageItems[1] ?? "", "'");

            if (!in_array($id, $unique_ids)) {
                $unique_ids[] = $id;
            } else {
                unset($pages[$key]);
            }
        }

        // 重建索引
        $pages = array_values($pages);

        // 排序 去掉头像
        $funcs = [];
        foreach ($pages as $key => $page) {
            $pageItems      = explode("\t", $page);
            $pageItems[5]   = "";
            $pages[$key]    = implode("\t", $pageItems);

            $funcs[] = $pageItems[3] ?? 0;
        }

        // 按照粉丝量进行排序
        array_multisort($funcs, SORT_DESC, $pages);

        $fileUniqueCount = count($pages);

        $locationPages  = [];
        $funsLowerPages = [];
        $excludePages   = [];

        $firstClassPage     = [];
        $secondClassPage    = [];
        $thirdClassPage     = [];

        $repeatCount = 0;

        $newPages = [];

        foreach ($pages as $page) {

            $pageItems = explode("\t", $page);

            if (empty($pageItems)) {
                continue;
            }

            $pageItems[0] = $pageItems[0] ?? "";                // name
            $pageItems[1] = trim($pageItems[1] ?? "", "'");     // id
            $pageItems[2] = $pageItems[2] ?? "";                // link
            $pageItems[3] = $pageItems[3] ?? "";                // likes
            $pageItems[4] = $pageItems[4] ?? "";                // page type
            $pageItems[5] = $pageItems[5] ?? "";                // image
            $pageItems[6] = str_replace(["\r", "\n", "\r\n"], "", $pageItems[6] ?? "");                // thunder type

            // 1. 挑选出来 pages 链接
            // if (str_contains($pageItems[2], ".com/pages/")) {
            //     $locationPages[] = $page;
            //     continue;
            // }

            // 录入数据库 排重
            // funslower 标志位 用于区分是否是处理粉丝少的专页
            // 如果是处理粉丝少的专页，则不进行总库排重，因为已经入了总库了
            if ($type !== "funslower") {
                // if ($redis->exists($pageItems[2])) {
                //     $repeatCount++;
                //     continue;
                // } else {
                //     $redis->set($pageItems[2], "1");
                //     $newPages[] = $pageItems[2];
                // }
            }

            // 2. 删除掉 小闪电 ✖️ 的专页
            if (in_array($pageItems[6], $this->excludeThunderType)) {
                $excludePages[] = $page;
                continue;
            }

            // 3. 挑选出来点赞量少于 100 的专页
            // 如果有 funslower 标志位，则不按照粉丝量进行过滤
            // if ($type !== "funslower" && $pageItems[3] < 100) {
            // $funsLowerPages[] = $page;
            // continue;
            // }

            // 4. 排除不传的教派 和 地区[从名字 和 专页类型两方面判断]
            if ($this->isExcludeSectAndLocation($pageItems[0], $pageItems[4])) {
                $excludePages[] = $page;
                continue;
            }

            // 5. 将剩余的专页分为三类
            foreach ($this->includeTypeKeywords as $keyword) {
                if (str_contains($pageItems[4], $keyword)) {
                    if ($keyword === "会") {
                        // 排除一些不是 教会 的类别
                        if (!preg_match("/社会|演唱会|音乐会|夜总会/", $pageItems[4])) {
                            $firstClassPage[] = CURRENT_DATE . "\t" . "A类" . "\t" . $page;
                            continue 2;
                        }
                    }
                    // “宗教” 可能会搜索出来其余的派别，这里用 最后一个小闪电的提示再过滤一次
                    else if ($keyword === "宗教") {
                        if (!empty($pageItems[6])) {
                            $firstClassPage[] = CURRENT_DATE . "\t" . "A类" . "\t" . $page;
                            continue 2;
                        }
                    } else {
                        $firstClassPage[] = CURRENT_DATE . "\t" . "A类" . "\t" . $page;
                        continue 2;
                    }
                }
            }

            if ($pageItems[4] === "社群" && !empty($pageItems[6])) {
                $firstClassPage[] = CURRENT_DATE . "\t" . "A类" . "\t" . $page;
                continue;
            }

            if (!empty($pageItems[6])) {
                $secondClassPage[] = CURRENT_DATE . "\t" . "B类" . "\t" . $page;
                continue;
            }

            $thirdClassPage[] = CURRENT_DATE . "\t" . "C类" . "\t" . $page;
        }

        // 将本次新增的链接保存到 pages
        if (!empty($newPages)) {
            file_put_contents(PAGE_DB_FILE, PHP_EOL . implode(PHP_EOL, $newPages), FILE_APPEND);
        }

        // 保存 排除的的专页 结果
        $content = implode(PHP_EOL, $excludePages);
        $fileName = PAGE_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . " exclude.tsv";
        file_put_contents($fileName, $content);

        // 保存 粉丝少的专页
        // if ($type !== "funslower") {
        //     $content = implode(PHP_EOL, $funsLowerPages);
        //     $fileName = PAGE_OUTPUT_FUNSLOWER_PATH . CURRENT_TIME . " funslower.tsv";
        //     file_put_contents($fileName, $content);
        // }

        // 保存 location 专页
        $content = implode(PHP_EOL, $locationPages);
        $fileName = PAGE_OUTPUT_LOCATION_PATH . CURRENT_TIME . " location.tsv";
        file_put_contents($fileName, $content);

        // 保存 第一批次 的专页
        $content = implode(PHP_EOL, $firstClassPage);
        $fileName = PAGE_OUTPUT_A_CLASS_PATH . CURRENT_TIME . " A.tsv";
        file_put_contents($fileName, $content);

        // 保存 第二批次 的专页
        $content = implode(PHP_EOL, $secondClassPage);
        $fileName = PAGE_OUTPUT_B_CLASS_PATH . CURRENT_TIME . " B.tsv";
        file_put_contents($fileName, $content);

        // 保存 第三批次 的专页
        $content = implode(PHP_EOL, $thirdClassPage);
        $fileName = PAGE_OUTPUT_C_CLASS_PATH . CURRENT_TIME . " C.tsv";
        file_put_contents($fileName, $content);

        // 输出结果
        $this->app->info(sprintf("专页共计 %d 个", $totalCount));
        $this->app->info(sprintf("自身排重后剩 %d 个", $fileUniqueCount));

        $totalUniqueCount = $fileUniqueCount - $repeatCount;
        // $this->app->info(sprintf("总库排重后 %d 个，不重复比例 %s", $totalUniqueCount, $totalUniqueCount / $fileUniqueCount * 100 . '%'));

        // 按照总库排重后的来计算。
        $this->app->info(sprintf("地区专页 %d 个，比例 %s", count($locationPages), count($locationPages) / $totalUniqueCount * 100 . "%"));
        $this->app->info(sprintf("不合原则专页 %d 个，比例 %s", count($excludePages), count($excludePages) / $totalUniqueCount * 100 . "%"));

        if ($type !== "funslower") {
            // $this->app->info(sprintf("粉丝少于100的专页 %d 个，比例 %s", count($funsLowerPages), count($funsLowerPages) / $totalUniqueCount * 100 . "%"));
        }

        $this->app->info(sprintf("A类专业 %d 个，比例 %s", count($firstClassPage), count($firstClassPage) / $totalUniqueCount * 100 . "%"));
        $this->app->info(sprintf("B类专业 %d 个，比例 %s", count($secondClassPage), count($secondClassPage) / $totalUniqueCount * 100 . "%"));
        $this->app->info(sprintf("C类专业 %d 个，比例 %s", count($thirdClassPage), count($thirdClassPage) / $totalUniqueCount * 100 . "%"));
    }


    // 给一个ID文件，将该文件中的ID加入总库
    private function addPageIntoTotal(array $pages)
    {
        // 将id写入 redis
        foreach ($pages as $page) {
            $id = str_replace(["\r", "\n", "\r\n"], "", $page);
            $this->redisClient->set($page, "1");
        }

        // 将文件写入 ids
        $appendStr = PHP_EOL . "----" .  CURRENT_DATE . "----" . PHP_EOL . implode(PHP_EOL, $pages);
        file_put_contents($this->db_file, $appendStr, FILE_APPEND);

        $this->app->info("已将ID加入总库");
    }

    // 排除不传的教派和地区
    private function isExcludeSectAndLocation($name, $type)
    {
        $excludes = array_merge($this->excludeArea, $this->excludeSect);
        foreach ($excludes as $exclude) {
            if (str_contains($name, $exclude) || str_contains($type, $exclude)) {
                return true;
            }
        }

        return false;
    }

    // 处理导出的专页
    public function handleUserPages() {
        
        $this->init();

        $files = glob(PAGE_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $pages = file_get_contents($files[0]);

        // 1. 替换链接
        $pages = str_replace("https://facebook", "https://www.facebook", $pages);
        $pages = str_replace("com/1000", "com/profile.php?id=1000", $pages);
        $pages = str_replace("com/615", "com/profile.php?id=615", $pages);

        // 2. 自身排重
        $pages = str_replace("\r", "", $pages);
        $pages = explode(PHP_EOL, $pages);

        $pagesCount = count($pages);

        $pages = array_unique($pages);
        
        // 3. 总库排重
        $newPageIds = [];
        $newPages = [];
        foreach ($pages as $page) {
            $pageArr = explode("\t", $page);
            $pageId  = str_replace("'", "", $pageArr[1]);
            
            if ($this->redisClient->exists($pageId)) {
                continue;
            }

            $newPages[] = $page;
            $newPageIds[] = $pageId;
        }

        // 4. 保存文件
        $this->addPageIntoTotal($newPageIds);

        // 5. 将新的专页写入 output 目录
        $output = PAGE_OUTPUT_PATH .  CURRENT_TIME . " new pages";
        file_put_contents($output, implode(PHP_EOL, $newPages));

        $this->app->info(sprintf("专页共计 %d 个, 新专页 %d 个", $pagesCount, count($newPages)));
    }
}
