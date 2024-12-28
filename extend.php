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
    ClarkWinkelmann\AuthorChangeEventSubscriber,
    Flarum\LikesEventSubscriber,
    Flarum\TagsEventSubscriber,
    FoF\MasqueradePurgeCacheMiddleware,
    FoF\MergeDiscussionsEventSubscriber,
    SychO\MovePostsSubscriber,
    v17development\FlarumBlogEventSubscriber
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
    StatusCodesCacheMiddleware,
    VaryCookieMiddleware
};
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Extend;
use Flarum\Foundation\Event\ClearingCache;
use Flarum\Http\Middleware\CheckCsrfToken;
use Flarum\Http\Middleware\InjectActorReference;
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
        ->default('acpl-lscache.drop_qs', implode("\n", LSCache::DEFAULT_DROP_QS))
        ->default('acpl-lscache.status_codes_cache', "404 3600\n403 3600\n500 120"),
    (new Extend\Event())->listen(Saved::class, Listener\UpdateSettingsListener::class),

    // Permissions
    (new Extend\ApiSerializer(UserSerializer::class))
        ->attribute(
            'canPurgeLSCache',
            fn (UserSerializer $serializer) => $serializer->getActor()->can('lscache.purge'),
        ),

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

    // Cache status codes
    (new Extend\Middleware('forum'))->insertAfter(InjectActorReference::class, StatusCodesCacheMiddleware::class),
    (new Extend\Middleware('admin'))->insertAfter(InjectActorReference::class, StatusCodesCacheMiddleware::class),
    (new Extend\Middleware('api'))->insertAfter(InjectActorReference::class, StatusCodesCacheMiddleware::class),

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

    // Extensions
    (new Extend\Conditional)
        ->whenExtensionEnabled('flarum-likes', [
            (new Extend\Event)->subscribe(LikesEventSubscriber::class),
        ])
        ->whenExtensionEnabled('flarum-tags', [
            (new Extend\Event)->subscribe(TagsEventSubscriber::class),
        ])
        ->whenExtensionEnabled('fof-masquerade', [
            (new Extend\Middleware('api'))->add(MasqueradePurgeCacheMiddleware::class),
        ])
        ->whenExtensionEnabled('fof-merge-discussions', [
            (new Extend\Event)->subscribe(MergeDiscussionsEventSubscriber::class),
        ])
        ->whenExtensionEnabled('v17development-blog', [
            (new Extend\Event)->subscribe(FlarumBlogEventSubscriber::class),
        ])
        ->whenExtensionEnabled('clarkwinkelmann-author-change', [
            (new Extend\Event)->subscribe(AuthorChangeEventSubscriber::class),
        ])
        ->whenExtensionEnabled('sycho-move-posts', [
            (new Extend\Event)->subscribe(MovePostsSubscriber::class),
        ]),
];
