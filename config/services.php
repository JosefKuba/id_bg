<?php

declare(strict_types=1);

return [
    /****************************************************************************
     * Application Services
     * --------------------------------------------------------------------------
     *
     * The services to be loaded for your application.
     *****************************************************************************/

    'services' => [
        'backup'    => \App\Services\BackupService::class,
        'file'      => \App\Services\FileService::class,
        'path'      => \App\Services\PathService::class,
        'id'        => \App\Services\IdService::class,
        'fish'      => \App\Services\FishService::class,
        'page'      => \App\Services\PageService::class,
        'group'     => \App\Services\GroupService::class,
        'command'   => \App\Services\CommandService::class,
        'redis'     => \App\Services\RedisService::class,
        'language'  => \App\Services\LanguageService::class,
        'friend'    => \App\Services\FriendService::class,
        'faith'     => \App\Services\FaithService::class,
        'area'      => \App\Services\AreaService::class,
        'link'      => \App\Services\LinkService::class,
        'rc'        => \App\Services\RcService::class,
        'post'      => \App\Services\PostService::class,
        'keyword'   => \App\Services\KeywordService::class,
        'avater'    => \App\Services\AvaterService::class,
        'name'      => \App\Services\NameService::class,
    ],
];
