<?php

use ACPL\FlarumCache\Utility\HtaccessManager;
use Flarum\Foundation\Paths;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $paths = resolve(Paths::class);
        $htaccessManager = new HtaccessManager($paths);
        $htaccessManager->updateHtaccess();
    },
    'down' => function (Builder $schema) {
        $paths = resolve(Paths::class);
        $htaccessManager = new HtaccessManager($paths);
        $htaccessManager->removeLsCacheBlock();
    }
];
