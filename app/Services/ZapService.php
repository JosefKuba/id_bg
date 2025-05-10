<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;

class ZapService implements ServiceInterface
{
    use Trait\SelectTrait;

    private $app;

    public function load(App $app): void
    {
        $this->app = $app;
    }

    // 从搜索结果中提取 zap 小组链接
    public function parse () {

        $files = glob(ZAP_INPUT_PATH . "*");

        $results = [];
        
        foreach ($files as $file) {

            $lines = getLine($file);

            foreach ($lines as $line) {
                if (!str_contains($line, "whatsapp") && !str_contains($line, "viber")) {
                    continue;
                }

                $line = str_replace("whatsapp. com", "whatsapp.com", $line);
                $line = str_replace("viber. com", "viber.com", $line);

                preg_match('/chat\.(?: )?whatsapp(?: )?\.com\/[0-9a-zA-Z]{22}/', $line, $matches);

                // 如果没有找到 WhatsApp，再匹配 Viber
                if (!$matches) {
                    preg_match('/invite\.(?: )?viber(?: )?\.com[0-9a-zA-Z%\/?=]+/', $line, $matches);
                    if (!$matches) {
                        continue;
                    }
                }

                $results[] = "https://" . str_replace(" ", "", $matches[0]);
            }
        }

        $outputPath = ZAP_OUTPUT_PATH . CURRENT_TIME . " result";
        file_put_contents($outputPath, implode(PHP_EOL, array_unique($results)));
    }

}
