<?php

use ACPL\FlarumCache\Utility\HtaccessManager;
use Flarum\Foundation\Paths;
use Flarum\Http\CookieFactory;
use Illuminate\Database\Schema\Builder;

function lsCacheGetHtAccessManager(): HtaccessManager
{
    $paths = resolve(Paths::class);
    $cookie = resolve(CookieFactory::class);
    return new HtaccessManager($paths, $cookie);
}

return [
    'up' => function (Builder $schema) {
        $htaccessManager = lsCacheGetHtAccessManager();
        $htaccessManager->updateHtaccess();
    },
    'down' => function (Builder $schema) {
        $htaccessManager = lsCacheGetHtAccessManager();
        $htaccessManager->removeLsCacheBlock();
    }
];
