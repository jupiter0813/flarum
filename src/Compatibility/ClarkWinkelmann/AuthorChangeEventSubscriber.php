<?php

namespace ACPL\FlarumLSCache\Compatibility\ClarkWinkelmann;

use ACPL\FlarumLSCache\Listener\{AbstractCachePurgeSubscriber, DiscussionCachePurgeTrait};
use ClarkWinkelmann\AuthorChange\Event\{DiscussionCreateDateChanged,
    DiscussionUserChanged,
    PostCreateDateChanged,
    PostEditDateChanged,
    PostUserChanged
};
use Illuminate\Contracts\Events\Dispatcher;

class AuthorChangeEventSubscriber extends AbstractCachePurgeSubscriber
{
    use DiscussionCachePurgeTrait;

    public function subscribe(Dispatcher $events): void
    {
        $this->addPurgeListener($events, DiscussionCreateDateChanged::class, [$this, 'handleDiscussion']);
        $this->addPurgeListener($events, DiscussionUserChanged::class, [$this, 'handleDiscussionUserChanged']);
        $this->addPurgeListener($events, PostCreateDateChanged::class, [$this, 'handlePost']);
        $this->addPurgeListener($events, PostEditDateChanged::class, [$this, 'handlePost']);
        $this->addPurgeListener($events, PostUserChanged::class, [$this, 'handlePostUserChanged']);
    }

    protected function handleDiscussion(DiscussionCreateDateChanged|DiscussionUserChanged $event): void
    {
        $this->handleDiscussionRelatedPurge();
        $this->purger->addPurgeTags([
            "discussion_{$event->discussion->id}",
            "user_{$event->discussion->user->id}",
            "user_{$event->discussion->user->username}",
        ]);
    }

    protected function handleDiscussionUserChanged(DiscussionUserChanged $event): void
    {
        $this->handleDiscussion($event);
        $this->purger->addPurgeTags([
            "user_{$event->oldUser->id}",
            "user_{$event->oldUser->username}",
        ]);
    }

    protected function handlePost(PostCreateDateChanged|PostEditDateChanged|PostUserChanged $event): void
    {
        $this->purger->addPurgeTags([
            "discussion_{$event->post->discussion->id}",
            'posts',
            "post_{$event->post->id}",
            "user_{$event->post->user->id}",
            "user_{$event->post->user->username}",
        ]);
    }

    protected function handlePostUserChanged(PostUserChanged $event): void
    {
        $this->handlePost($event);
        $this->purger->addPurgeTags([
            "user_{$event->oldUser->id}",
            "user_{$event->oldUser->username}",
        ]);
    }
}
