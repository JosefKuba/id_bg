<?php

/**
 * 根据文件名获取内容数组
 */
function getLine($file)
{
    $lines = file($file);
    return array_map(function ($id) {
        return str_replace(["\r", "\n", "\r\n"], "", $id);
    }, $lines);
}

// 检测汉字
function containsChinese($str)
{
    return preg_match('/[\x{4e00}-\x{9fa5}]/u', $str);
}

// 检测日文
function containsJapanese($str)
{
    // 正则表达式模式，匹配平假名、片假名和汉字
    return preg_match("/[\x{3041}-\x{3096}]/u", $str) || preg_match('/[\x{FF61}-\x{FF9F}]/u', $str);
}

// 检测希伯来语
function containsHebrew($text) 
{
    return preg_match('/[\x{0590}-\x{05FF}]/u', $text);
}

// 检测阿拉伯语
function containsArabic($text)
{
    return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
}

/**
 * 使用 FastText 检测语言（无需写入临时文件）
 *
 * @param string $text 输入文本
 * @return string|null 返回语言代码（如 "en", "hu", "zh"），检测失败返回 null
 */
function detectLanguage(string $text): ?string
{
    $fasttextPath = '/home/tomas/www/fastText/fasttext';      // fasttext 可执行文件路径
    $modelPath    = '/home/tomas/www/fastText/lid.176.ftz';   // 模型路径

    if (!file_exists($fasttextPath) || !file_exists($modelPath)) {
        return null;
    }

    // 安全清理文本，去除可能引起命令错误的字符
    $cleanText = preg_replace('/[^\p{L}\p{N}\s\.\,\-\'\"]+/u', '', $text);
    $cleanText = trim($cleanText);

    if ($cleanText === '') {
        return null;
    }

    // 使用 echo + 管道的方式传递文本给 fasttext
    // 注意使用双引号包裹整个命令，确保不被 shell 注入
    $command = sprintf(
        'echo %s | %s predict %s -',
        escapeshellarg($cleanText),           // 确保安全转义内容
        escapeshellcmd($fasttextPath),
        escapeshellarg($modelPath)
    );

    $output = shell_exec($command);

    if (!$output) {
        return null;
    }

    $label = trim($output); // e.g., __label__hu
    if (strpos($label, '__label__') === 0) {
        return substr($label, 9);
    }

    return null;
}