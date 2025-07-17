<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class GroupService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    private $redisClient;

    private $db_file;

    private $funsCount = 2000;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    public function init($type)
    {
        switch ($type) {
            case 'user':
                $this->redisClient  = $this->app->redis->getUserGroupClient();
                $this->db_file      = USER_GROUPS_DB_FILE;
                break;

            case 'search':
                $this->redisClient  = $this->app->redis->getSearchGroupClient();
                $this->db_file      = SEARCH_GROUPS_DB_FILE;
                break;

            case 'promote':
                $this->redisClient  = $this->app->redis->getPromoteGroupClient();
                $this->db_file      = PROMOTE_GROUPS_DB_FILE;
                break;
                
            default:
                $this->app->error("未设置 redis 客户端");
                exit;
        }
    }

    // 处理推广小组
    public function handlePromoteGroups($filePath)
    {
        $this->init("promote");

        // 提取链接
        $lines = getLine($filePath);

        $groupIds = array_map(function($line){
            // https://fb.com/379113791949410_122127553976399699
            if (preg_match("/https:\/\/fb\.com\/\d+_\d+/", $line)) {
                return "";
            }

            // https://fb.com/groups/1664744957127087/posts/3979659835635576
            preg_match("/https:\/\/fb\.com\/groups\/([^\/]+)/", $line, $matches);

            return $matches[1] ?? "";
        }, $lines);

        $groupIds = array_unique($groupIds);

        $newGroupIds = array_filter($groupIds, function($groupId) {
            return $groupId && !$this->redisClient->exists($groupId);
        });

        // 新小组加入总库
        $this->addGroupsIntoTotal($newGroupIds);

        $output = GROUP_OUTPUT_PATH .  CURRENT_TIME . " new groups";
        file_put_contents($output, implode(PHP_EOL, $newGroupIds));

        $this->app->info(sprintf("新小组 %d 个", count($newGroupIds)));
    }


    // 处理导出的小组
    public function handleUserGroups($filePath)
    {

        $this->init("user");

        $groups = file_get_contents($filePath);

        // 1. 替换链接
        $groups = str_replace("https://facebook", "https://www.facebook", $groups);
        $groups = str_replace("com/1000", "com/profile.php?id=1000", $groups);
        $groups = str_replace("com/615", "com/profile.php?id=615", $groups);

        // 2. 自身排重
        $groups = str_replace("\r", "", $groups);
        $groups = explode(PHP_EOL, $groups);

        $allGroupsCount = count($groups);

        $_groups = [];
        foreach ($groups as $group) {
            $groupArr       = explode("\t", $group);
            $id             = str_replace(["\r", "\n", "\r\n", "'"], "", $groupArr[1] ?? "");
            $_groups[$id]   = $group;
        }
        $groups = array_values($_groups);

        $uniqueGroupsCount = count($groups);

        // 3. 处理数据

        $newGroupIds = [];
        $newGroups = [];
        $privateGroups = [];
        $funcsFewerGroups = [];

        // 总库中不存在的小组
        $notExistGroupIds = [];

        // 计数器
        $funcsFewerGroupsCount = 0;
        $privateGroupsCount = 0;
        $existGroupsCount = 0;

        foreach ($groups as $group) {

            // 去掉 " 对 Google 表格的影响
            $group = str_replace('"', "", $group);

            $groupArr = explode("\t", $group);

            // 过滤掉格式不对的行
            if (!isset($groupArr[1])) {
                continue;
            }

            $groupId  = str_replace("'", "", $groupArr[1]);

            // 总库排重
            if ($this->redisClient->exists($groupId)) {
                $existGroupsCount++;
                continue;
            } else {
                $notExistGroupIds[] = $groupId;
            }

            // 过滤掉人数少的小组
            if ($groupArr[3] < 100) {
                $funcsFewerGroups[] = $group;
                $funcsFewerGroupsCount++;
                continue;
            }

            // 挑选出来私密小组和人数太少的小组
            $type = $this->getPublic($groupArr[4]);
            if ($type !== '公开') {
                $privateGroups = [];
                $privateGroupsCount++;
                continue;
            }

            // 小组头像会导致卡，去掉小组头像
            $newGroups[] = implode("\t", [$groupArr[0], $groupArr[1], $groupArr[2], $groupArr[3], $groupArr[4]]);
            $newGroupIds[] = $groupId;
        }

        // 4. 新小组加入总库
        $this->addGroupsIntoTotal($notExistGroupIds);

        // 保存文件
        $output = GROUP_OUTPUT_PUBLIC_PATH .  CURRENT_TIME . " user.tsv";
        file_put_contents($output, implode(PHP_EOL, $newGroups));

        $output = GROUP_OUTPUT_PRIVATE_PATH .  CURRENT_TIME . " user.tsv";
        file_put_contents($output, implode(PHP_EOL, $privateGroups));

        $output = GROUP_OUTPUT_FUNSLOWER_PATH .  CURRENT_TIME . " user.tsv";
        file_put_contents($output, implode(PHP_EOL, $funcsFewerGroups));

        // 输出信息
        $this->app->info(sprintf("小组共计 %d 个, 不重复小组 %d 个", $allGroupsCount, $uniqueGroupsCount));
        $this->app->info(sprintf("和总库重复 %d 个, 新小组 %d 个", $existGroupsCount, $uniqueGroupsCount - $existGroupsCount));
        $this->app->info(sprintf("和总库重复 %d 个", $funcsFewerGroupsCount));

        $this->app->info(sprintf("私密小组 %d 个", $privateGroupsCount));
        $this->app->info(sprintf("剩余小组 %d 个", count($newGroupIds)));
    }

    // 处理搜索的小组
    public function handleSearchGroups($filePath)
    {
        /*
            1. 删除掉 小闪电 ✖️ 的小组
            2. 删除掉 不传派别 的小组 + 澳门地区小组
            3. 剩下的小组分为 公开、私密、人数少
        */

        $this->init("search");

        $groups = file_get_contents($filePath);

        // 0. 替换链接
        $groups = str_replace("https://facebook", "https://www.facebook", $groups);

        // 1. 自身排重
        $groups = explode(PHP_EOL, $groups);

        $totalGroupsCount = count($groups);

        $_groups = [];
        foreach ($groups as $group) {
            $groupArr       = explode("\t", $group);
            $id             = str_replace(["\r", "\n", "\r\n", "'"], "", $groupArr[1] ?? "");
            $_groups[$id]   = $group;
        }

        $groups = array_values($_groups);

        $uniqueGroupsCount = count($groups);

        // 收集统计数据
        $funcsFewerGroups   = [];
        $checkGroups        = [];
        $privateGroups      = [];
        $checkGroupIds      = [];
        $excludeGroups      = [];
        // $arabGroups         = [];

        $repeatGroupsCount = 0;


        echo "不重复小组 {$uniqueGroupsCount} 个" . PHP_EOL;

        // 先过滤掉和总库重复的行 和 不是小组的行
        foreach ($groups as $key => $group) {

            // 去掉 " 对 Google 表格的影响
            $group = str_replace('"', "", $group);

            $groupArr = explode("\t", $group);

            $title      = $groupArr[0] ?? "";
            $id         = str_replace(["\r", "\n", "\r\n", "'"], "", $groupArr[1] ?? "");
            $funsCount  = $groupArr[3] ?? "";

            // 过滤掉不是小组数据的行
            if (!preg_match("/^\d+$/", $id)) {
                unset($groups[$key]);
                continue;
            }

            // 过滤掉总库中有的行
            if ($this->redisClient->exists($id)) {
                $repeatGroupsCount++;
                unset($groups[$key]);
                continue;
            }
        }

        $groups = array_values($groups);

        $newSearchCount = count($groups);

         echo "新搜索小组 {$newSearchCount} 个" . PHP_EOL;

        // 检测标题语言
        $startTime = time();
        $isHu = $notHu = 0;

        foreach ($groups as $key => $group) {
            
            // 去掉 " 对 Google 表格的影响
            $group = str_replace('"', "", $group);

            $groupArr = explode("\t", $group);

            $title      = $groupArr[0] ?? "";
            $id         = str_replace(["\r", "\n", "\r\n", "'"], "", $groupArr[1] ?? "");
            $funsCount  = $groupArr[3] ?? "";

            if ($key % 100 == 0 && $key > 1) {
                $endTime = time();
                echo $key . " / " . $uniqueGroupsCount . "; 耗时 " . ($endTime - $startTime) . " 秒; "  . "{$isHu} / ${notHu}" .  PHP_EOL;
                $startTime = time();

                $isHu = $notHu = 0;
            }

            // 过滤掉标题不是荷兰语的小组
            if (detectLanguage($title) !== "nl") {
                $excludeGroups[] = $group;
                $notHu++;
                continue;
            } else {
                $isHu++;
            }
            
            $checkGroups[]   = $group;
            $checkGroupIds[] = $id;
        }

        // 重建索引
        $checkGroups = array_values($checkGroups);

        // 新的小组加入总库
        $this->addGroupsIntoTotal($checkGroupIds);

        // 区分 基督教、天主教、佛教、按照类别区分小组
        foreach ($checkGroups as $key => $group) {
            $groupArr   = explode("\t", $group);
            $title      = $groupArr[0] ?? "";
            $id         = $groupArr[1] ?? "";
            $link       = $groupArr[2] ?? "";
            $funs       = $groupArr[3] ?? "";
            $public     = $groupArr[4] ?? "";
            $desc       = $groupArr[5] ?? "";

            // 统一 public
            $public = $this->getPublic($public);

            // 重新构建 checkGroups
            $checkGroups[$key] = implode("\t", [$title, $id, $link, $funs, $public, $desc]);
        }

        // 分类：公开人数多、公开人数少、私密小组 阿拉伯小组
        foreach ($checkGroups as $key => $group) {
            $groupArr   = explode("\t", $group);
            
            $funsCount  = $groupArr[3] ?? "";
            $public     = $groupArr[4] ?? "";

            // 单独保留下来所有的标题中有 阿拉伯语的小组
            // if (containsArabic($group)) {
            //     $arabGroups[] = $group;
            // }

            if ($public !== "公开") {
                $privateGroups[] = $group;
                unset($checkGroups[$key]);
                continue;
            }

            if ($funsCount < $this->funsCount) {
                $funcsFewerGroups[] = $group;
                unset($checkGroups[$key]);
                continue;
            }
        }

        // 重建索引
        $checkGroups = array_values($checkGroups);

        // 保存结果
        if ($excludeGroups) {
            $path = GROUP_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . " search.tsv";
            file_put_contents($path, implode(PHP_EOL, $excludeGroups));
        }

        if ($checkGroups) {
            $path = GROUP_OUTPUT_PUBLIC_PATH . CURRENT_TIME . " search.tsv";
            file_put_contents($path, implode(PHP_EOL, $checkGroups));
        }

        if ($privateGroups) {
            $path = GROUP_OUTPUT_PRIVATE_PATH . CURRENT_TIME . " search.tsv";
            file_put_contents($path, implode(PHP_EOL, $privateGroups));
        }

        if ($funcsFewerGroups) {
            $path = GROUP_OUTPUT_FUNSLOWER_PATH . CURRENT_TIME . " search.tsv";
            file_put_contents($path, implode(PHP_EOL, $funcsFewerGroups));
        }

        // if ($arabGroups) {
        //     $path = GROUP_OUTPUT_PATH . CURRENT_TIME . " arab.tsv";
        //     file_put_contents($path, implode(PHP_EOL, $arabGroups));
        // }

        // 打印结果
        $percent = number_format($uniqueGroupsCount / $totalGroupsCount * 100, "1") . "%";
        $this->app->info(sprintf("小组共计 %d 个，自身去重后 %d 个，不重复比例 %s", $totalGroupsCount, $uniqueGroupsCount, $percent));

        $this->app->info(sprintf("非希伯来小组共计 %d 个", count($excludeGroups)));

        $this->app->info(sprintf("和总库重复小组共计 %d 个", $repeatGroupsCount));

        $this->app->info(sprintf("私密小组共计 %d 个", count($privateGroups)));

        $this->app->info(sprintf("{$this->funsCount}以下新小组共计 %d 个", count($funcsFewerGroups)));

        $percent = number_format(count($checkGroups) / $uniqueGroupsCount * 100, "1") . "%";
        $this->app->info(sprintf("{$this->funsCount}以上新小组 %d 个，剩存比例 %s", count($checkGroups), $percent));

        // $this->app->info(sprintf("阿拉伯小组共计 %d 个", count($arabGroups)));
    }

    // 给一个ID文件，将该文件中的ID加入总库
    private function addGroupsIntoTotal(array $groups)
    {
        // 将id写入 redis
        foreach ($groups as $group) {
            $id = str_replace(["\r", "\n", "\r\n"], "", $group);
            $this->redisClient->set($group, "1");
        }

        // 将文件写入 ids
        $appendStr = PHP_EOL . "----" .  CURRENT_DATE . "----" . PHP_EOL . implode(PHP_EOL, $groups);
        file_put_contents($this->db_file, $appendStr, FILE_APPEND);

        $this->app->info("已将ID加入总库");
    }

    // 根据小组名称匹配类型
    public function addType($file)
    {
        $lines = getLine($file);

        $results = [];
        $excludeGroups = [];

        foreach ($lines as $key => $line) {

            $lineArr = explode("\t", $line);
            $title   = $lineArr[0] ?? "";

            // 排除掉标题中没有中文的专页 或者 标题中有日文的专页
            if (!$this->isChinese($title)) {
                $excludeGroups[] = $line;
                unset($lines[$key]);
                continue;
            }

            // 过滤掉不传到教派 & 反面关键词
            if ($this->isNegative($title)) {
                $excludeGroups[] = $line;
                unset($lines[$key]);
                continue;
            }

            $type = $this->getType($title);

            $results[] =  $type . "\t" . $line;
        }

        file_put_contents(GROUP_OUTPUT_PATH . CURRENT_TIME  . " good.tsv", implode(PHP_EOL, $lines));
        file_put_contents(GROUP_OUTPUT_PATH . CURRENT_TIME  . " bad.tsv", implode(PHP_EOL, $excludeGroups));

        file_put_contents(GROUP_OUTPUT_PATH . CURRENT_TIME  . " types.tsv", implode(PHP_EOL, $results));
    }

    // 判断小组标题的语言
    private function isChinese($title)
    {
        return \containsChinese($title) && !containsJapanese($title);
    }

    // 判断是否是 不传的教派 或 有反面关键词
    private function isNegative($title)
    {
        $excludes       = array_merge($this->excludeArea, $this->excludeSect, $this->evalKeywords["include"]);
        $excludesPreg   = "/" . implode("|", $excludes) . "/";
        $includePreg    = "/" . implode("|", $this->evalKeywords["exclude"]) . "/";

        return preg_match($excludesPreg, $title) && !preg_match($includePreg, $title);
    }

    // 统一小组是否公开
    private function getPublic($public)
    {
        switch ($public) {
            case "公开":
            case "公開":
            case "公开小组":
            case "公開社團":
            case "public":
            case "Public":
            case "Public group":
                $public = "公开";
                break;

            case "不公开":
            case "非公开":
            case "私密":
            case "private":
            case "Private":
            case "Private group":
            case "私人":
                $public = "私密";
                break;
        }

        return $public;
    }

    // 根据标题判断类别
    private function getType($title)
    {
        $type = "";

        // 判断 基督教 天主教 佛教 犹太教
        $judaismPregInclude    = "/" . implode("|", $this->judaismKeywords["include"]) . "/";
        $judaismPregExclude    = "/" . implode("|", $this->judaismKeywords["exclude"]) . "/";

        $christianPregInclude          = "/" . implode("|", $this->christianKeywords["include"]) . "/";
        $christianPregExclude          = "/" . implode("|", $this->christianKeywords["exclude"]) . "/";

        $catholicPregInclude           =  "/" . implode("|", $this->catholicKeywords["include"]) . "/";
        $catholicPregExclude           =  "/" . implode("|", $this->catholicKeywords["exclude"]) . "/";

        $buddhistPregInclude    = "/" . implode("|", $this->buddhistKeywords["include"]) . "/";
        $buddhistPregExclude    = "/" . implode("|", $this->buddhistKeywords["exclude"]) . "/";

        if (preg_match($buddhistPregInclude, $title) && !preg_match($buddhistPregExclude, $title)) {
            $type = "佛教";
        } else if (preg_match($judaismPregInclude, $title) && !preg_match($judaismPregExclude, $title)) {
            $type = "犹太教";
        } else if (preg_match($christianPregInclude, $title) && !preg_match($christianPregExclude, $title)) {
            $type = "基督教";
        } else if (preg_match($catholicPregInclude, $title) && !preg_match($catholicPregExclude, $title)) {
            $type = "天主教";
        } else {
            // 区分 外邦类型
            foreach ($this->typesKeywords as $_type => $keywords) {
                $typePregInclude = "/" . implode("|", $keywords["include"]) . "/";
                $typePregExclude = "/" . implode("|", $keywords["exclude"]) . "/";
                if (preg_match($typePregInclude, $title) && !preg_match($typePregExclude, $title)) {
                    $type = $_type;
                    break;
                }
            }

            if (empty($type)) {
                $type = "";
            }
        }

        return $type;
    }

    // 挑选名称
    public function selectName($file)
    {
        $lines = file($file);

        $chineseNameIds   = [];
        $chineseNames     = [];

        $chinesePinYin    = [];
        // $chinesePinYinIds = [];

        $notChineseNames  = [];

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id      = $lineArr[0] ?? "";
            $name    = str_replace(["\r", "\n", "\r\n"], "", $lineArr[1] ?? "");

            // 过滤到死账号
            if (str_contains($name, "Facebook")) {
                continue;
            }

            // 先检测是否是汉字
            if (\containsChinese($name)) {
                $chineseNameIds[]   = $id;
                $chineseNames[]     = $line;
                continue;
            } else {
                // 没有汉字的情况下，检查英文中是否有中文拼音
                $preg_1 = "/^(" . implode("|", $this->nameKeywords) . ") /";
                $preg_2 = "/ (" . implode("|", $this->nameKeywords) . ")$/";
                if (preg_match($preg_1, $name) || preg_match($preg_2, $name)) {
                    $chinesePinYin[]    = $line;
                    continue;
                }

                $notChineseNames[] = $line;
            }
        }

        // 中文
        $outputFileName = GROUP_OUTPUT_PATH . CURRENT_TIME . " chinese.tsv";
        file_put_contents($outputFileName, implode("", $chineseNames));

        // 中文 ID
        $outputFileName = GROUP_OUTPUT_PATH . CURRENT_TIME . " chinese id.tsv";
        file_put_contents($outputFileName, implode(PHP_EOL, $chineseNameIds));

        // 拼音
        $outputFileName = GROUP_OUTPUT_PATH . CURRENT_TIME . " chinese pinyin.tsv";
        file_put_contents($outputFileName, implode("", $chinesePinYin));

        // 非中文
        $outputFileName = GROUP_OUTPUT_PATH . CURRENT_TIME . " not chinese.tsv";
        file_put_contents($outputFileName, implode("", $notChineseNames));
    }

    // 测试小组成员所在的地区
    public function detectUser($group, $area, $type)
    {
        $groupLines = getLine($group);
        $areaLines  = getLine($area);

        $groupUsers = [];
        foreach ($groupLines as $line) {
            $lineArr = explode("\t", $line);
            $userId = str_replace(["'", "\r", "\n", "\r\n"], "", $lineArr[0] ?? "");
            $groupId = str_replace(["'", "\r", "\n", "\r\n"], "", $lineArr[5] ?? "");

            if (empty($groupId) || empty($userId)) {
                continue;
            }

            $groupUsers[$groupId][] = $userId;;
        }


        $userAreas = [];
        foreach ($areaLines as $line) {
            $lineArr = explode("\t", $line);
            $userId = str_replace(["'", "\r", "\n", "\r\n"], "", $lineArr[0] ?? "");
            $userAreas[$userId] = $line;
        }

        // 根据地区分库
        $cities = match ($type) {
            'ao' => $this->aoCitys,
            'mz' => $this->mzCitys,
        };


        $wifiGoodPreg = "/" . implode("|", $cities['wifi_good']) . "/";
        $wifiAvaragePreg = "/" . implode("|", $cities['wifi_average']) . "/";

        // 开始检测

        $output[] = "小组ID\t网络好\t比例\t网络一般\t比例\t其余地区\t比例";
        foreach ($groupUsers as $groupId => $userIds) {

            $wifiGoodCount = $wifiAvarageCount = $wifiOtherCount = 0;

            foreach ($userIds as $userId) {
                if (preg_match($wifiGoodPreg, $userAreas[$userId])) {
                    $wifiGoodCount++;
                } else if (preg_match($wifiAvaragePreg, $userAreas[$userId])) {
                    $wifiAvarageCount++;
                } else {
                    $wifiOtherCount++;
                }
            }

            $output[] = sprintf(
                "%d\t%d\t%s\t%d\t%s\t%d\t%s",
                $groupId,
                $wifiGoodCount,
                number_format($wifiGoodCount / count($userIds) * 100, "1") . "%",
                $wifiAvarageCount,
                number_format($wifiAvarageCount / count($userIds) * 100, "1") . "%",
                $wifiOtherCount,
                number_format($wifiOtherCount / count($userIds) * 100, "1") . "%",
            );
        }

        $path = GROUP_OUTPUT_PATH . CURRENT_TIME . " results.tsv";
        file_put_contents($path, implode(PHP_EOL, $output));
    }

    // 按照地区分类小组
    public function detectArea($filePath) {
                
        // 世俗派地区
        $worldArea = [
            "Tel Aviv",
            "Haifa",
            "Herzliya",
            "Eilat",
            "Ramat Gan",
            "Petah Tikva",
            "Rishon Lezion",
            "Ashkelon",
            "Kfar Saba",
            "Nahariya",
            "Kiryat Motzkin",
            "Kiryat Yam",
            "Beersheba",
            "特拉维夫",
            "海法",
            "赫兹利亚",
            "埃拉特",
            "拉马特甘",
            "佩塔提克瓦",
            "里雄莱锡安",
            "阿什克隆",
            "卡法尔萨巴",
            "纳哈里亚",
            "基利亚特莫兹金",
            "基利亚特亚姆",
            "贝尔谢巴",
        ];

        // 阿拉伯语地区
        $arabArea = [
            "Nazareth",
            "Rahat",
            "Umm al-Fahm",
            "Tayibe",
            "Shefa-Amr",
            "Shefar'am",
            "Tamra",
            "Sakhnin",
            "Baqa al-Gharbiyye",
            "Tira",
            "Ar'ara",
            "Arraba",
            "Kafr Qasim",
            "Maghar",
            "Qalansawe",
            "Qalansuwa",
            "Kafr Kanna",
            // 
            "拿撒勒",
            "拉哈特",
            "乌姆·法赫姆",
            "塔耶贝",
            "谢法·阿姆尔",
            "谢法尔·阿姆",
            "塔姆拉",
            "萨赫尼因",
            "巴卡·阿尔-加尔比耶",
            "提拉",
            "阿尔阿拉",
            "阿尔拉巴",
            "卡夫尔·卡西姆",
            "马赫尔",
            "卡兰萨韦",
            "卡兰苏瓦",
            "卡夫尔·卡纳",
        ];

        // 俄语地区
        $ruArea = [
            "特拉维夫",
            "Tel Aviv",
            "阿什杜德",
            "Ashdod",
            "海法",
            "Haifa",
            "内坦亚",
            "Netanya",
            "巴特亚姆",
            "Bat Yam",
            "阿里耶尔",
            "Ariel",
        ];

                
        $groups = getLine($filePath);

        $areaGroups = [];
        foreach ($groups as $key => $group) {
            
            // 小组类型
            $type = [];

            $groupArr   = explode("\t", $group);
            $name       = $groupArr[0];
            $areas      = $groupArr[7];

            $areasArr = explode(", ", $areas);

            $areasArr = array_map(function($area){
                return trim($area);
            }, $areasArr);

            // 排查 世俗派 小组
            foreach ($worldArea as $area) {
                if (str_contains($name, $area)) {
                    $type[] = "世俗派";
                }
            }

            foreach ($areasArr as $area) {
                if (in_array($area, $worldArea)) {
                    $type[] = "世俗派";
                }
            }

            // 排查 阿拉伯语言 小组
            foreach ($arabArea as $area) {
                if (str_contains($name, $area)) {
                    $type[] = "阿拉伯语地区";
                }
            }

            foreach ($areasArr as $area) {
                if (in_array($area, $arabArea)) {
                    $type[] = "阿拉伯语地区";
                }
            }

            // 排查 俄语 小组
            foreach ($ruArea as $area) {
                if (str_contains($name, $area)) {
                    $type[] = "俄语地区";
                }
            }

            foreach ($areasArr as $area) {
                if (in_array($area, $ruArea)) {
                    $type[] = "俄语地区";
                }
            }

            $type = array_unique($type);

            if ($type) {
                $areaGroups[$key] = $group . "\t" . implode(", ", $type);
            }
        }

        $path = GROUP_OUTPUT_PATH . CURRENT_TIME . " area.tsv";
        file_put_contents($path, implode(PHP_EOL, $areaGroups));
    }
}
