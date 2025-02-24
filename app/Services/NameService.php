<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class NameService implements ServiceInterface
{
    private $app;

    // 定义保加利亚、塞尔维亚、马其顿、土耳其、希腊的姓氏特征
    private $bulgarian_suffixes = ["ov", "ova", "ev", "eva", "ski", "ska", "in", "ina"];
    private $serbian_suffixes = ["ić", "ović"];
    private $macedonian_suffixes = ["ski", "ska", "ovski", "ovska"];
    private $turkish_chars = ["ş", "ç", "ü", "ö", "ı", "ğ"];
    private $greek_suffixes = ["idis", "poulos", "theodor", "maratheftis"];

    // 西里尔字母检测（用于判断是否可能是保加利亚名字）
    private $cyrillic_pattern = "/[А-Яа-яЁё]/u";

    // 希腊字母检测（用于判断是否是希腊名字）
    private $greek_letters_pattern = "/[Α-Ωα-ω]/u";

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 挑选保加利亚名字
    public function selectBgName($file)
    {
        $lines = getLine($file);

        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);
            $id = $lineArr[0];
            $name = mb_strtolower($lineArr[1]);

            if (empty($id) || empty($name)) {
                continue;
            }

            $country = $this->checkName($name);

            switch ($country) {
                case '保加利亚':
                case '马其顿':
                    $right[] = $line . "\t" . $country;
                    $rightId[] = $id;
                    continue 2;
                    break;

                case '不确定':
                    $notSure[] = $line;
                    continue 2;
                    break;

                default:
                    $error[] = $line . "\t" . $country;
                    break;
            }
        }

        $outputPath = NAME_OUTPUT_PATH . CURRENT_TIME . " bg.tsv";
        file_put_contents($outputPath, implode(PHP_EOL, $right));

        $outputPath = NAME_OUTPUT_PATH . CURRENT_TIME . " bg id.tsv";
        file_put_contents($outputPath, implode(PHP_EOL, $rightId));

        $outputPath = NAME_OUTPUT_PATH . CURRENT_TIME . " not sure.tsv";
        file_put_contents($outputPath, implode(PHP_EOL, $notSure));

        $outputPath = NAME_OUTPUT_PATH . CURRENT_TIME . " not bg.tsv";
        file_put_contents($outputPath, implode(PHP_EOL, $error));

        $this->app->info(sprintf(
            "ID共计 %d 个，保加利亚ID %d 个，非保加利亚ID %d 个，不确定地区ID %d 个，合格比例 %s",
            count($lines),
            count($right),
            count($error),
            count($notSure),
            number_format(count($right) * 100 / count($lines), 1) . '%'
        ));
    }

    // 检测函数
    function checkName($name)
    {
        $name_lower = mb_strtolower($name, 'UTF-8');

        // 检测土耳其字符
        foreach ($this->turkish_chars as $char) {
            if (mb_stripos($name_lower, $char) !== false) {
                return '土耳其';
            }
        }

        // 检查希腊字母
        if (preg_match($this->greek_letters_pattern, $name)) {
            return '希腊';
        }

        // 保加利亚保留
        foreach ($this->bulgarian_suffixes as $suffix) {
            if (mb_substr($name_lower, -mb_strlen($suffix), null, 'UTF-8') === $suffix) {
                return '保加利亚';
            }
        }

        // 塞尔维亚的不保留
        foreach ($this->serbian_suffixes as $suffix) {
            if (mb_substr($name_lower, -mb_strlen($suffix), null, 'UTF-8') === $suffix) {
                return '塞尔维亚';
            }
        }

        // 马其顿保留
        foreach ($this->macedonian_suffixes as $suffix) {
            if (mb_substr($name_lower, -mb_strlen($suffix), null, 'UTF-8') === $suffix) {
                return '马其顿';
            }
        }

        // 希腊的不保留
        foreach ($this->greek_suffixes as $suffix) {
            if (mb_substr($name_lower, -mb_strlen($suffix), null, 'UTF-8') === $suffix) {
                return '希腊';
            }
        }

        // 检测西里尔字母
        // 先检测过塞尔维亚的后缀，再检测西里尔字母，结果更加准确
        if (preg_match($this->cyrillic_pattern, $name)) {
            return '保加利亚';
        }

        // 检测不出来的保留下来刷脸
        // 后续跑地区再检测
        return '不确定';
    }
}
