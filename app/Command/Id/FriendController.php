<?php

declare(strict_types=1);

namespace App\Command\Id;

use Minicli\Command\CommandController;

/**
 * 排查深宗账号时
 *  - 和已经排查过的排重
 *  - 排除掉弟兄姊妹的账号
 */

class FriendController extends CommandController
{
    public function desc()
    {
        return [
            'command'   => 'php artisan id friend',
            'desc'      => '排查深宗好友ID：排重 排除弟兄姊妹 入库',
        ];
    }

    public function help()
    {
        echo "\n";
        echo "作用：将找深宗账号时，跑用户详细信息的ID与总库 排重 过滤掉弟兄姊妹账号 入库\n";
        echo "输入：data/id/input/  目录下的 id 文件，支持一次处理多个文件\n";
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
        // 1. 备份原始文件
        $backupService = $this->getApp()->backup;
        $backupService->backupInput();

        // 2. 将数据文件汇总
        $fileService = $this->getApp()->file;
        $fileService->merge();

        // 3. 处理ID
        $csvFiles = $fileService->getFiles();

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
        $idService->removeDuplicatesAndAddIntoTotal($outputFileName, "friends");

        // 排除弟兄姊妹的账号
        $fishService = $this->getApp()->fish;
        $fishService->removeDXZM($outputFileName);
    }
}
