<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 将 csv 文件中的 ID 汇总去重
 *  支持多个文件
 */

class DefaultController extends CommandController
{
    public function desc()
    {
        return [
            'command' => 'php artisan id',
            'desc'    => '刷脸ID: 排重 查询彩球标记 入库'
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：将刷脸的ID与总库排重 入库\n";
        echo "输入：data/id/input/  目录下的 id 文件，支持一次处理多个文件\n";
        echo "输出：data/id/output/ 目录下的未查询彩球标记的ID 或者 是对应目录下查询过彩球标记的ID\n";
        echo "两个库之间会互相排重，避免重复刷脸。在另一个库中检测到的合格的ID，会放在 data/id/output/ 目录下\n";

        echo "\t php artisan id         入台湾库\n\t php artisan id type=my 入马来库\n";
        echo "\t php artisan id --nf    只入库，不查询彩球标记\n";
        echo "\n";
    }

    public function handle(): void
    {
        if ($this->hasFlag("help")) {
            $this->help();
        } else {
            $this->exec();
        }
    }

    public function exec(): void
    {
        $startTime = time();

        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput();

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge();

        // 3. 处理ID
        $csvFiles = $fileService->getCsvFiles();

        if (empty($csvFiles)) {
            $this->error("input 文件夹中缺少文件");
            exit();
        }

        $file = $csvFiles[0];

        $idService = $this->getApp()->id;
        $uniqueIds = $idService->getUniqueIdFromFile($file);

        $outputStr = implode(PHP_EOL, $uniqueIds);
        $outputFileName = ID_OUTPUT_PATH . CURRENT_TIME . " unique";
        file_put_contents($outputFileName, $outputStr);

        $allIdCount = $idService->getAllIdCountFromFile($file);

        // 5. 清空 input 文件夹
        $fileService->clearFolder();

        // 6. 输出结果
        $pathService = $this->getApp()->path;
        $relativeFilePath = $pathService->getRelativePath($outputFileName);
        $percent = number_format(count($uniqueIds) * 100 / $allIdCount, 1) . '%';
        $outputInfo = sprintf("文件合并完成，ID共 %d 个，不重复ID %d 个，不重复比例 %s \n输出文件名：%s", $allIdCount, count($uniqueIds), $percent, $relativeFilePath);
        $this->info($outputInfo);

        // 7. 总库排重，并且将新的ID加入总库
        $type = $this->hasParam("type") ? $this->getParam("type") : "";
        $idService->removeDuplicatesAndAddIntoTotal($outputFileName, $type);

        // --no-fish
        if ($this->hasFlag("nf") || $this->hasFlag("no-fish")) return;

        // 8. 查询彩球标记
        $fishService = $this->getApp()->fish;
        $fishResult = $fishService->getFish($outputFileName);

        // 将查询好的标记保存到文件中
        $collect_file_name = ID_OUTPUT_COLLECT_PATH . CURRENT_TIME . ".csv";
        file_put_contents($collect_file_name, implode(PHP_EOL, $fishResult['collect']));

        $aside_file_name = ID_OUTPUT_ASIDE_PATH . CURRENT_TIME . ".csv";
        file_put_contents($aside_file_name, implode(PHP_EOL, $fishResult['aside']));

        $exclude_file_name = ID_OUTPUT_EXCLUDE_PATH . CURRENT_TIME . ".csv";
        file_put_contents($exclude_file_name, implode(PHP_EOL, $fishResult['exclude']));

        unlink($outputFileName);

        $endTime = time();

        $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
