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

function containsChinese($str)
{
    return preg_match('/[\x{4e00}-\x{9fa5}]/u', $str);
}


function containsJapanese($str)
{
    // 正则表达式模式，匹配平假名、片假名和汉字
    return preg_match("/[\x{3041}-\x{3096}]/u", $str) || preg_match('/[\x{FF61}-\x{FF9F}]/u', $str);
}
