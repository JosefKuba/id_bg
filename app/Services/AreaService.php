<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class AreaService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 挑选地区
    public function selectArea($filePath, $type)
    {
        $lines = getLine($filePath);

        $citys = match ($type) {
            'tw' => $this->twCitys,
            'my' => $this->myCitys,
            'uk' => $this->ukCitys,
            'tm' => $this->myCitys + $this->twCitys,
        };

        $result = $_result = $ids = [];
        $preg = "/" . implode("|", $citys) . "/";
        foreach ($lines as $line) {
            $lineArr = explode("\t", $line);

            if (preg_match($preg, $line)) {
                $ids[] = $lineArr[0];
                $result[] = $line;
            } else {
                $_result[] = $line;
            }
        }

        $path = AREA_OUTPUT_PATH . CURRENT_TIME . " " . $type . " id";
        file_put_contents($path, implode(PHP_EOL, $ids));

        $path = AREA_OUTPUT_PATH . CURRENT_TIME . " " . $type;
        file_put_contents($path, implode(PHP_EOL, $result));

        $path = AREA_OUTPUT_PATH . CURRENT_TIME . " not " . $type;
        file_put_contents($path, implode(PHP_EOL, $_result));
    }

    // 增加地区
    public function addArea($filePath)
    {
        $lines = getLine($filePath);

        $areas = [
            '台湾' => $this->twCitys,
            '马来' => $this->myCitys,
            '英国' => $this->ukCitys,
        ];

        $result = [];


        foreach ($lines as $line) {
            foreach ($areas as $area => $citys) {
                $preg = "/" . implode("|", $citys) . "/";
                if (preg_match($preg, $line)) {
                    $result[] = $line . "\t" . $area;
                    continue 2;
                }
            }

            $result[] = $line . "\t" . "";
        }

        $path = AREA_OUTPUT_PATH . CURRENT_TIME . " add";
        file_put_contents($path, implode(PHP_EOL, $result));
    }

    // 根据地区获取国家名称
    public function getCountry($file) 
    {
        $allAreas = getLine($file);

        $areaChunks = array_chunk($allAreas, 100);

        $results = [];
        
        $i = 0;

        foreach ($areaChunks as $areas) {

            foreach ($areas as $area) {

                $query = urlencode($area);
                
                $url = "https://nominatim.openstreetmap.org/search?format=json&accept-language=en&q=" . $query;

                $opts = [
                    "http" => [
                        "header" => "User-Agent: PHP-Geocoder/1.0\r\n"
                    ]
                ];
                $context = stream_context_create($opts);

                $start = time();

                $result = file_get_contents($url, false, $context);

                $end = time();

                if ($result === FALSE) {
                    $results[] = $area . "\t" . "--";
                    continue;
                }

                $data = json_decode($result, true);

                if (!empty($data)) {
                    $display_name = $data[0]['display_name'];
                    $parts = explode(",", $display_name);
                    $country = trim(end($parts));

                    $results[] = $area . "\t" . $country;
                    continue;
                }

                $results[] = $area . "\t" . "--";
            
                // 写入文件
                $path = AREA_OUTPUT_PATH . CURRENT_TIME . " country.tsv";
                file_put_contents($path, $area . "\t" . $country . PHP_EOL, FILE_APPEND);

                $i++;

                echo $i . " / " . count($allAreas) . "; " . $country . " 耗时: " . ($end - $start) . " 秒" . PHP_EOL;
            }
        }
    }

}
