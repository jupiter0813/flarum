<?php

namespace ACPL\FlarumLSCache\Compatibility\Flarum;

use ACPL\FlarumLSCache\Listener\{AbstractCachePurgeSubscriber, DiscussionCachePurgeTrait};
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\{Deleted as DiscussionDeleted,
    Hidden as DiscussionHidden,
    Renamed as DiscussionRenamed,
    Restored as DiscussionRestored,
    Started as DiscussionStarted
};
use Flarum\Post\Event\{Deleted as PostDeleted, Hidden as PostHidden, Posted, Restored as PostRestored};
use Flarum\Tags\Event\DiscussionWasTagged;
use Flarum\Tags\Tag;
use Illuminate\Contracts\Events\Dispatcher;

class TagsEventSubscriber extends AbstractCachePurgeSubscriber
{
    use DiscussionCachePurgeTrait;

    public function subscribe(Dispatcher $events): void
    {
        $this->addPurgeListener($events, DiscussionWasTagged::class, [$this, 'handleDiscussionWasTagged']);

        $discussionEvents = [
            DiscussionDeleted::class, DiscussionHidden::class, DiscussionRenamed::class, DiscussionRestored::class,
            DiscussionStarted::class,
        ];
        foreach ($discussionEvents as $event) {
            $this->addPurgeListener($events, $event, [$this, 'handleDiscussionEvents']);
        }

        $postEvents = [PostDeleted::class, PostHidden::class, Posted::class, PostRestored::class];
        foreach ($postEvents as $event) {
            $this->addPurgeListener($events, $event, [$this, 'handlePostEvents']);
        }
    }

    public function handleDiscussionWasTagged(DiscussionWasTagged $event): void
    {
        $this->handleDiscussionRelatedPurge();
        $this->purger->addPurgeTags([
            "discussion_{$event->discussion->id}",
            "user_{$event->discussion->user->id}",
            "user_{$event->discussion->user->username}",
            'tags',
            ...$this->generateCacheTagsForDiscussionTags($event->discussion),
        ]);
    }

    public function handleDiscussionEvents(
        DiscussionDeleted|DiscussionHidden|DiscussionRenamed|DiscussionRestored|DiscussionStarted $event,
    ): void {
        if (
            ($event instanceof DiscussionDeleted && $event->discussion->hidden_at !== null)
            /** @phpstan-ignore-next-line  Access to an undefined property Flarum\Discussion\Discussion::$is_approved. */
            || $event->discussion->is_approved === false
        ) {
            return;
        }

        $this->purger->addPurgeTags([
            'tags',
            ...$this->generateCacheTagsForDiscussionTags($event->discussion),
        ]);
    }

    public function handlePostEvents(PostDeleted|PostHidden|Posted|PostRestored $event): void
    {
        if (
            ($event instanceof PostRestored && $event->post->discussion->hidden_at !== null)
            /** @phpstan-ignore-next-line  Access to an undefined property Flarum\Discussion\Discussion::$is_approved. */
            || $event->post->discussion->is_approved === false
        ) {
            return;
        }

        $this->purger->addPurgeTags([
            'tags',
            ...$this->generateCacheTagsForDiscussionTags($event->post->discussion),
        ]);
    }

    protected function generateCacheTagsForDiscussionTags(Discussion $discussion): array
    {
        /** @phpstan-ignore-next-line Access to an undefined property Flarum\Discussion\Discussion::$tags. */
        return $discussion->tags->map(fn (Tag $tag) => "tag_$tag->slug")->toArray();
    }
}
