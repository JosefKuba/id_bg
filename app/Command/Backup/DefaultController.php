<?php

declare(strict_types=1);

namespace App\Command\Backup;

use Minicli\Command\CommandController;

use Google\Client;
use Google\Service\Drive;


class DefaultController extends CommandController
{

    public function desc()
    {
        return [
            'command'   => 'php artisan backup',
            'desc'      => '备份该项目的 rdb 文件',
        ];
    }

    public function help()
    {
        echo "这是帮助手册" . PHP_EOL;
    }

    public function handle(): void
    {
        if ($this->hasFlag("help")) {
            $this->help();
        } else {
            $this->exec();
        }
    }

    // 
    public function exec(): void
    {
        $folderId = $_ENV['GOOGLE_DRIVE_FOLDER_ID'];

        $filePath = DATA_PATH . "database/dump.rdb";
        $fileName = CURRENT_TIME . ".rdb";

        $id = $this->uploadFileToDrive($filePath, $fileName, 'application/octet-stream', $folderId);
        
        $this->info(sprintf("数据备份完成, ID 为: %s", $id));
    }

    // 1. 设置 Google 服务账号客户端
    public function getServiceClient() {
        $client = new Client();
        $client->setAuthConfig(ROOT_PATH . 'secrets/' . $_ENV['SCRIET_FILE']); // 替换为你的 JSON 文件路径
        $client->addScope(Drive::DRIVE_FILE);
        return new Drive($client);
    }

    // 2. 上传文件到 Google Drive
    public function uploadFileToDrive($filePath, $fileName, $mimeType, $folderId = null) {
        $driveService = $this->getServiceClient();

        // 文件元数据
        $fileMetadata = [
            'name' => $fileName
        ];
        if ($folderId) {
            $fileMetadata['parents'] = [$folderId]; // 指定文件夹 ID
        }

        $content = file_get_contents($filePath);

        $file = new Drive\DriveFile($fileMetadata);

        $uploadedFile = $driveService->files->create($file, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart'
        ]);

        return $uploadedFile->id;
    }
}
