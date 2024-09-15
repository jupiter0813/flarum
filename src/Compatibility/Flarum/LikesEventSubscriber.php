<?php

namespace ACPL\FlarumLSCache\Compatibility\Flarum;

use ACPL\FlarumLSCache\Listener\AbstractCachePurgeSubscriber;
use Flarum\Likes\Event\{PostWasLiked, PostWasUnliked};
use Illuminate\Contracts\Events\Dispatcher;

class LikesEventSubscriber extends AbstractCachePurgeSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        foreach ([PostWasLiked::class, PostWasUnliked::class] as $event) {
            $this->addPurgeListener($events, $event, [$this, 'handle']);
        }
    }

    protected function handle(PostWasLiked|PostWasUnliked $event): void
    {
        $this->purger->addPurgeTags([
            'posts',
            "post_{$event->post->id}",
            "discussion_{$event->post->discussion->id}",
            "user_{$event->post->user->id}",
            "user_{$event->post->user->username}",
            "user_{$event->user->id}",
            "user_{$event->user->username}",
        ]);
    }
}
