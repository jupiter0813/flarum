<?php

/*
 * This file is part of acpl/flarum-lscache.
 *
 * Copyright (c) android.com.pl.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ACPL\FlarumCache;

use ACPL\FlarumCache\Api\Controller\LSCacheCsrfResponseController;
use ACPL\FlarumCache\Api\Controller\PurgeLSCacheController;
use ACPL\FlarumCache\Command\LSCacheClearCommand;
use ACPL\FlarumCache\Listener\ClearingCacheListener;
use ACPL\FlarumCache\Middleware\LoginMiddleware;
use ACPL\FlarumCache\Middleware\LogoutMiddleware;
use ACPL\FlarumCache\Middleware\LSCacheControlMiddleware;
use ACPL\FlarumCache\Middleware\LSCachePurgeMiddleware;
use ACPL\FlarumCache\Middleware\LSTagsMiddleware;
use ACPL\FlarumCache\Middleware\VaryCookieMiddleware;
use Flarum\Extend;
use Flarum\Foundation\Event\ClearingCache;
use Flarum\Http\Middleware\CheckCsrfToken;
use Flarum\Http\Middleware\StartSession;
use Flarum\Settings\Event\Saved;

return [
    (new Extend\Frontend('admin'))->js(__DIR__.'/js/dist/admin.js'),
    (new Extend\Frontend('forum'))->js(__DIR__.'/js/dist/forum.js'),
    new Extend\Locales(__DIR__.'/locale'),

    // Settings
    (new Extend\Settings())
        ->default('acpl-lscache.cache_enabled', true)
        ->default('acpl-lscache.public_cache_ttl', 604_800)
        ->default('acpl-lscache.clearing_cache_listener', true)
        ->default('acpl-lscache.drop_qs', implode("\n", LSCache::DEFAULT_DROP_QS)),
    (new Extend\Event())->listen(Saved::class, Listener\UpdateSettings::class),

    // Vary cookie
    (new Extend\Middleware('forum'))->insertAfter(StartSession::class, VaryCookieMiddleware::class),
    (new Extend\Middleware('admin'))->insertAfter(StartSession::class, VaryCookieMiddleware::class),
    (new Extend\Middleware('api'))->insertAfter(StartSession::class, VaryCookieMiddleware::class),
    // LogIn
    (new Extend\Middleware('forum'))->insertAfter(VaryCookieMiddleware::class, LoginMiddleware::class),
    // LogOut
    (new Extend\Middleware('forum'))->insertAfter(VaryCookieMiddleware::class, LogoutMiddleware::class),

    // Tag routes
    (new Extend\Middleware('forum'))->add(LSTagsMiddleware::class),
    (new Extend\Middleware('api'))->add(LSTagsMiddleware::class),

    // Cache routes
    (new Extend\Middleware('forum'))->insertAfter(CheckCsrfToken::class, LSCacheControlMiddleware::class),
    (new Extend\Middleware('api'))->insertAfter(CheckCsrfToken::class, LSCacheControlMiddleware::class),

    // A workaround for the CSRF cache issue. The JS script fetches this path to update the CSRF
    (new Extend\Routes('api'))->get('/lscache-csrf', 'lscache.csrf', LSCacheCsrfResponseController::class),

    // Purge cache on update
    (new Extend\Middleware('forum'))->add(LSCachePurgeMiddleware::class),
    (new Extend\Middleware('admin'))->add(LSCachePurgeMiddleware::class),
    (new Extend\Middleware('api'))->add(LSCachePurgeMiddleware::class),

    // Purge cache
    (new Extend\Routes('api'))->get('/lscache-purge', 'lscache.purge', PurgeLSCacheController::class),
    (new Extend\Console())->command(LSCacheClearCommand::class),
    (new Extend\Event())->listen(ClearingCache::class, ClearingCacheListener::class),
];
