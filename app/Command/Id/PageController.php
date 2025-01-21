<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 导专页排重库
 *  - 导专页点赞专页
 *  - 导专页相关专页
 *  - 导用户点赞专页
 */

class PageController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id page',
            'desc'      => '已导专页的排重库 [专页编号]',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：导出专页时，将【专页编号】与总库 排重 入库\n";
        echo "输入：data/id/input/  目录下的 id 文件，支持一次处理多个文件\n";
        echo "格式：专页名称    专页编号    专页链接\n";
        echo "输出：data/id/output/ 新的文件\n";
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
        /*
            需要做什么？
            - 给一个ID文件，排查
            - 去掉弟兄姊妹的账号
        */

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
        $idService->removeDuplicatesAndAddIntoTotal($outputFileName, "groups");

        $endTime = time();

        $this->success(sprintf("数据处理完成，用时 %s 秒", $endTime - $startTime));
    }
}
