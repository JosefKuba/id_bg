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

            default:
                $this->app->error("未设置 redis 客户端");
                exit;
        }
    }

    // 处理导出的小组
    public function handleUserGroups() {
        
        $this->init("user");

        $files = glob(GROUP_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $groups = file_get_contents($files[0]);

        // 1. 替换链接
        $groups = str_replace("https://facebook", "https://www.facebook", $groups);
        $groups = str_replace("com/1000", "com/profile.php?id=1000", $groups);
        $groups = str_replace("com/615", "com/profile.php?id=615", $groups);

        // 2. 自身排重
        $groups = str_replace("\r", "", $groups);
        $groups = explode(PHP_EOL, $groups);

        $groupsCount = count($groups);

        $groups = array_unique($groups);
        
        // 3. 总库排重
        $newGroupIds = [];
        $newGroups = [];

        $funcsFewerCount = 0;
        $privateCount = 0;
        foreach ($groups as $group) {
            $groupArr = explode("\t", $group);

            if (!isset($groupArr[1])) {
                continue;
            }

            $groupId  = str_replace("'", "", $groupArr[1]);
            
            if ($this->redisClient->exists($groupId)) {
                continue;
            }

            // 过滤掉人数少的小组
            $funs = $groupArr[3];
            if ($funs < 100) {
                $funcsFewerCount++;
                continue;
            }

            // 挑选出来私密小组和人数太少的小组
            $type = $this->getPublic($groupArr[4]);
            if ($type !== '公开') {
                $privateCount++;
                continue;
            }

            // 小组头像会导致卡，去掉小组头像
            $newGroups[] = implode("\t", [$groupArr[0], $groupArr[1], $groupArr[2], $groupArr[3], $groupArr[4], $groupArr[5]]);
            $newGroupIds[] = $groupId;
        }

        // 4. 保存文件
        $this->addGroupsIntoTotal($newGroupIds);

        // 5. 将新的专页写入 output 目录
        $output = GROUP_OUTPUT_PATH .  CURRENT_TIME . " new groups";
        file_put_contents($output, implode(PHP_EOL, $newGroups));

        $this->app->info(sprintf("小组共计 %d 个, 新小组 %d 个", $groupsCount, count($newGroups)));
    }

    // 处理搜索的小组
    public function handleSearchGroups()
    {
        /*
            1. 删除掉 小闪电 ✖️ 的小组
            2. 删除掉 不传派别 的小组 + 澳门地区小组
            3. 剩下的小组分为 公开、私密、人数少
        */

        $this->init("search");

        $files = glob(GROUP_INPUT_PATH . "*");

        if (empty($files)) {
            $this->app->error("input 目录下缺少文件");
            exit;
        }

        $groups = file_get_contents($files[0]);

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
        $funcsFewerGroups = [];
        // $funcsFewerGroups400 = [];
        // $excludeGroups = [];
        $checkGroups = [];
        $checkGroupIds = [];

        $repeatGroupsCount = 0;
        // $notChineseGroupsCount = 0;

        foreach ($groups as $group) {

            // group
            $groupArr = explode("\t", $group);

            $title      = $groupArr[0] ?? "";
            $id         = str_replace(["\r", "\n", "\r\n", "'"], "", $groupArr[1] ?? "");
            $funsCount  = $groupArr[3] ?? "";

            // 过滤掉不是小组数据的行
            if (!preg_match("/^\d+$/", $id)) {
                continue;
            }

            // 过滤掉总库中有的行
            if ($this->redisClient->exists($id)) {
                $repeatGroupsCount++;
                continue;
            }

            // 排除掉标题中没有中文的专页 或者 标题中有日文的专页
            // if (!$this->isChinese($title)) {
            //     $notChineseGroupsCount++;
            //     continue;
            // }

            // 过滤掉不传到教派 & 反面关键词
            // if ($this->isNegative($title)) {
            //     $excludeGroups[] = $group;
            //     continue;
            // }

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
            // $type       = "";

            // 统一 public
            $public = $this->getPublic($public);

            // 判断 小组类别
            // $type = $this->getType($title);

            // 重新构建 checkGroups
            $checkGroups[$key] = implode("\t", [$title, $id, $link, $funs, $public]);
        }

        // 把人数少的小组挑出来
        // $buddhistGroups = [];
        foreach ($checkGroups as $key => $group) {
            // group
            $groupArr   = explode("\t", $group);
            $funsCount  = $groupArr[3] ?? "";
            // $sect       = str_replace(["\r", "\n", "\r\n"], "", $groupArr[0] ?? "");

            // 先把佛教挑出来
            // if ($sect === "佛教") {
            //     $buddhistGroups[] = $group;
            //     unset($checkGroups[$key]);
            //     continue;
            // }

            // $isChristren = in_array($sect, ["基督教", "天主教"]);

            // if ($isChristren && $funsCount < 500) {
            //     $funcsFewerGroups[] = $group;
            //     unset($checkGroups[$key]);
            // }

            // if (!$isChristren && $funsCount < 2000) {
            //     if ($funsCount < 400) {
            //         $funcsFewerGroups400[] = $group;
            //     } else {
            //         $funcsFewerGroups[] = $group;
            //     }

            //     unset($checkGroups[$key]);
            // }

            if ($funsCount < 2000) {
                $funcsFewerGroups[] = $group;
                unset($checkGroups[$key]);
            }
        }

        // 重建索引
        $checkGroups = array_values($checkGroups);

        // 保存结果
        if ($checkGroups) {
            $newGroupsPath = GROUP_OUTPUT_PUBLIC_PATH . CURRENT_TIME . ".tsv";
            file_put_contents($newGroupsPath, implode(PHP_EOL, $checkGroups));
        }

        // if ($excludeGroups) {
        //     $excludeGroupsPath = GROUP_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . ".tsv";
        //     file_put_contents($excludeGroupsPath, implode(PHP_EOL, $excludeGroups));
        // }

        // if ($buddhistGroups) {
        //     $buddhistGroupsPath =  GROUP_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . " buddhist.tsv";
        //     file_put_contents($buddhistGroupsPath, implode(PHP_EOL, $buddhistGroups));
        // }

        if ($funcsFewerGroups) {
            $funcsGroupsPath = GROUP_OUTPUT_FUNSLOWER_PATH . CURRENT_TIME . ".tsv";
            file_put_contents($funcsGroupsPath, implode(PHP_EOL, $funcsFewerGroups));
        }

        // if ($funcsFewerGroups400) {
        //     $funcsGroupsPath400 = GROUP_OUTPUT_FUNSLOWER_PATH . CURRENT_TIME . " 400.tsv";
        //     file_put_contents($funcsGroupsPath400, implode(PHP_EOL, $funcsFewerGroups400));
        // }

        // 打印结果
        $percent = number_format($uniqueGroupsCount / $totalGroupsCount * 100, "1") . "%";
        $this->app->info(sprintf("小组共计 %d 个，自身去重后 %d 个，不重复比例 %s", $totalGroupsCount, $uniqueGroupsCount, $percent));

        $this->app->info(sprintf("和总库重复小组共计 %d 个", $repeatGroupsCount));
        
        // $this->app->info(sprintf("不合格小组共计 %d 个", count($excludeGroups)));
        // $this->app->info(sprintf("佛教小组共计 %d 个", count($buddhistGroups)));
        // $this->app->info(sprintf("外文小组共计 %d 个", $notChineseGroupsCount));

        $this->app->info(sprintf("2000以下新小组共计 %d 个", count($funcsFewerGroups)));

        $percent = number_format(count($checkGroups) / $uniqueGroupsCount * 100, "1") . "%";
        $this->app->info(sprintf("2000以上新小组 %d 个，剩存比例 %s", count($checkGroups), $percent));
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

        // 判断 基督教 天主教 佛教
        $christianPregInclude          = "/" . implode("|", $this->christianKeywords["include"]) . "/";
        $christianPregExclude          = "/" . implode("|", $this->christianKeywords["exclude"]) . "/";

        $catholicPregInclude           =  "/" . implode("|", $this->catholicKeywords["include"]) . "/";
        $catholicPregExclude           =  "/" . implode("|", $this->catholicKeywords["exclude"]) . "/";

        $buddhistPregInclude    = "/" . implode("|", $this->buddhistKeywords["include"]) . "/";
        $buddhistPregExclude    = "/" . implode("|", $this->buddhistKeywords["exclude"]) . "/";

        if (preg_match($buddhistPregInclude, $title) && !preg_match($buddhistPregExclude, $title)) {
            $type = "佛教";
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
}
