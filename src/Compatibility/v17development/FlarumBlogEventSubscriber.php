<?php

namespace ACPL\FlarumLSCache\Compatibility\v17development;

use ACPL\FlarumLSCache\Event\LSCachePurging;
use ACPL\FlarumLSCache\Listener\AbstractCachePurgeSubscriber;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use V17Development\FlarumBlog\Event\BlogMetaSaving;

class FlarumBlogEventSubscriber extends AbstractCachePurgeSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $this->addPurgeListener($events, BlogMetaSaving::class, [$this, 'handle']);
        $this->addPurgeListener($events, LSCachePurging::class, [$this, 'handleLSCachePurging']);
    }

    public function handle(BlogMetaSaving $event): void
    {
        $this->purger->addPurgeTags([
            'blog.overview',
            /** @phpstan-ignore-next-line  Access to an undefined property V17Development\FlarumBlog\BlogMeta\BlogMeta::$discussion_id. */
            "blog_{$event->blogMeta->discussion_id}",
        ]);
    }

    /**
     * If discussion is detected, also purge blog, because blog is a discussion.
     */
    public function handleLSCachePurging(LSCachePurging $event): void
    {
        if (in_array('index', $event->data['tags'])) {
            $this->purger->addPurgeTag('blog.overview');
        }

        $discussions = Arr::where($event->data['tags'], fn (string $tag) => preg_match('/^discussion_\d+$/', $tag));
        if (! empty($discussions)) {
            $this->purger->addPurgeTags(
                preg_replace('/^discussion_(\d+)$/', 'blog_$1', $discussions),
            );
        }
    }
}
