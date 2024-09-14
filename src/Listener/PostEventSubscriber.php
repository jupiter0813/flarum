<?php

namespace ACPL\FlarumLSCache\Listener;

use Flarum\Approval\Event\PostWasApproved;
use Flarum\Post\Event\{Deleted, Hidden, Posted, Restored, Revised};
use Illuminate\Contracts\Events\Dispatcher;

class PostEventSubscriber extends AbstractCachePurgeSubscriber
{
    use DiscussionCachePurgeTrait;

    public function subscribe(Dispatcher $events): void
    {
        $postEvents = [Hidden::class, Posted::class, Restored::class, PostWasApproved::class, Revised::class];
        foreach ($postEvents as $event) {
            $this->addPurgeListener($events, $event, [$this, 'handle']);
        }
    }

    protected function handle(Hidden|Posted|Restored|PostWasApproved|Revised $event): void
    {
        if (! $this->shouldPurge($event)) {
            return;
        }

        $this->purger->addPurgeTags([
            'posts',
            "discussion_{$event->post->discussion_id}",
            "user_{$event->post->user_id}",
            "user_{$event->post->user_id}",
        ]);

        if (! $event instanceof Revised) {
            $this->handleDiscussionRelatedPurge();
        }
    }

    protected function shouldPurge(Deleted|Hidden|Posted|Restored|Revised|PostWasApproved $event): bool
    {
        return ! (
            $event->post->discussion->is_private
            /** @phpstan-ignore-next-line  Access to an undefined property Flarum\Post\Post::$is_approved. */
            || $event->post->is_approved === false
        );
    }
}
