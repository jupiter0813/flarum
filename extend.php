<?php

/*
 * This file is part of acpl/flarum-lscache.
 *
 * Copyright (c) android.com.pl.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ACPL\FlarumLSCache;

use ACPL\FlarumLSCache\Api\Controller\{LSCacheCsrfResponseController, PurgeLSCacheController};
use ACPL\FlarumLSCache\Command\LSCachePurgeCommand;
use ACPL\FlarumLSCache\Compatibility\{
    ClarkWinkelmann\AuthorChange\ClarkWinkelmannAuthorChangeEventSubscriber,
    Flarum\Likes\FlarumLikesEventSubscriber,
    Flarum\Tags\FlarumTagsEventSubscriber,
    FriendsOfFlarum\Masquerade\FofMasqueradePurgeCacheMiddleware,
    SychO\MovePosts\SychOMovePostsSubscriber,
    v17development\FlarumBlog\FlarumBlogEventSubscriber
};
use ACPL\FlarumLSCache\Listener\{
    ClearingCacheListener,
    DiscussionEventSubscriber,
    PostEventSubscriber,
    UserEventSubscriber
};
use ACPL\FlarumLSCache\Middleware\{
    CacheControlMiddleware,
    CacheTagsMiddleware,
    LoginMiddleware,
    LogoutMiddleware,
    PurgeCacheMiddleware,
    VaryCookieMiddleware
};
use Flarum\Extend;
use Flarum\Foundation\Event\ClearingCache;
use Flarum\Http\Middleware\CheckCsrfToken;
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
    (new Extend\Event())->listen(Saved::class, Listener\UpdateSettingsListener::class),

    // Vary cookie
    (new Extend\Middleware('forum'))->insertAfter(CheckCsrfToken::class, VaryCookieMiddleware::class),
    (new Extend\Middleware('admin'))->insertAfter(CheckCsrfToken::class, VaryCookieMiddleware::class),
    (new Extend\Middleware('api'))->insertAfter(CheckCsrfToken::class, VaryCookieMiddleware::class),
    // LogIn
    (new Extend\Middleware('forum'))->insertAfter(VaryCookieMiddleware::class, LoginMiddleware::class),
    // LogOut
    (new Extend\Middleware('forum'))->insertAfter(VaryCookieMiddleware::class, LogoutMiddleware::class),

    // Tag routes
    (new Extend\Middleware('forum'))->add(CacheTagsMiddleware::class),
    (new Extend\Middleware('api'))->add(CacheTagsMiddleware::class),

    // Cache routes
    (new Extend\Middleware('forum'))->insertAfter(VaryCookieMiddleware::class, CacheControlMiddleware::class),
    (new Extend\Middleware('api'))->insertAfter(VaryCookieMiddleware::class, CacheControlMiddleware::class),

    // A workaround for the CSRF cache issue. The JS script fetches this path to update the CSRF
    (new Extend\Routes('api'))->get('/lscache-csrf', 'lscache.csrf', LSCacheCsrfResponseController::class),

    // Purge cache on update
    (new Extend\Middleware('forum'))->add(PurgeCacheMiddleware::class),
    (new Extend\Middleware('admin'))->add(PurgeCacheMiddleware::class),
    (new Extend\Middleware('api'))->add(PurgeCacheMiddleware::class),

    // Purge cache
    (new Extend\Routes('api'))->get('/lscache-purge', 'lscache.purge', PurgeLSCacheController::class),
    (new Extend\Console)->command(LSCachePurgeCommand::class),
    (new Extend\Event)->listen(ClearingCache::class, ClearingCacheListener::class),

    (new Extend\Event)->subscribe(DiscussionEventSubscriber::class),
    (new Extend\Event)->subscribe(PostEventSubscriber::class),
    (new Extend\Event)->subscribe(UserEventSubscriber::class),

    (new Extend\Conditional)
        ->whenExtensionEnabled('flarum-likes', [
            (new Extend\Event)->subscribe(FlarumLikesEventSubscriber::class),
        ])
        ->whenExtensionEnabled('flarum-tags', [
            (new Extend\Event)->subscribe(FlarumTagsEventSubscriber::class),
        ])
        ->whenExtensionEnabled('fof-masquerade', [
            (new Extend\Middleware('api'))->add(FofMasqueradePurgeCacheMiddleware::class),
        ])
        ->whenExtensionEnabled('v17development-blog', [
            (new Extend\Event)->subscribe(FlarumBlogEventSubscriber::class),
        ])
        ->whenExtensionEnabled('clarkwinkelmann-author-change', [
            (new Extend\Event)->subscribe(ClarkWinkelmannAuthorChangeEventSubscriber::class),
        ])
        ->whenExtensionEnabled('sycho-move-posts', [
            (new Extend\Event)->subscribe(SychOMovePostsSubscriber::class),
        ]),
];
