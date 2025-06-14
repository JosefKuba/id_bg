<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;
use TextAnalysis\Tokenizers\GeneralTokenizer;

class KeywordService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    private $specialChars = "?(– .️.()“”<^>!:;÷=—_~[]#«»…1234567890️“️„*'$@%^*&/+-’\"";

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 从文本中解析关键词
    public function parse($file)
    {
        $text = file_get_contents($file);
        $tokenizer = new GeneralTokenizer();
        $keywords = $tokenizer->tokenize($text);

        $keywords = array_unique($keywords);

        $keywords = array_map(function($keyword){

            // 使用正则表达式匹配并移除表情符号
            $keyword = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1FA70}-\x{1FAFF}]/u', '', $keyword);
            
            if (!$keyword) {
                return "";
            }

            $keyword = trim($keyword, $this->specialChars);

            // 过滤 �
            $keyword = preg_replace('/[^\P{C}\n]+/u', '', $keyword);

            return $keyword;
        }, $keywords);

        // 拆分包含 . () 的单词
        $newWords = [];
        $checkChars = ['.', '(', ')', ';', ':', '"', '“', '”', '[', ']', '/', '„'];
        foreach ($keywords as $key => $keyword) {
            if (!$keyword) {
                continue;
            }
            foreach ($checkChars as $char) {
                if (str_contains($keyword, $char)) {
                    $keywrodArr = explode($char, $keyword);
                    $keywords[$key] = $keywrodArr[0];
                    array_push($newWords, $keywrodArr[1]);
                }
            }
        }

        $keywords = array_merge($keywords, $newWords);

        // 再次过滤特殊字符
        $keywords = array_map(function($keyword){
            if (!$keyword) return "";
            
            // 过滤 �
            $keyword = preg_replace('/[^\P{C}\n]+/u', '', $keyword);

            return mb_strtolower(trim($keyword, $this->specialChars));
        }, $keywords);

        // 最后过滤关键词长度
        $keywords = array_filter($keywords, function($keyword){
            if (!$keyword) {
                return false;
            }
            $len = mb_strlen($keyword);
            return $len > 3 && $len < 20;
        });

        // 排序 去重
        sort($keywords);
        $keywords = array_unique($keywords);

        $path = KEYWORD_OUTPUT_PATH . CURRENT_TIME . " keywords";
        file_put_contents($path, implode(PHP_EOL, $keywords));

    }
}
