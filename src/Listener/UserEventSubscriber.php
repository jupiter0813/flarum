<?php

namespace ACPL\FlarumLSCache\Listener;

use Flarum\User\Event\{AvatarChanged, Deleting, GroupsChanged, Renamed};
use Illuminate\Contracts\Events\Dispatcher;

class UserEventSubscriber extends AbstractCachePurgeSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $shared = [AvatarChanged::class, Deleting::class, GroupsChanged::class, Renamed::class];
        foreach ($shared as $event) {
            $this->addPurgeListener($events, $event, [$this, 'handleUserWithPosts']);
        }
    }

    /** Purge discussions where user has posted. */
    public function handleUserWithPosts(AvatarChanged|Deleting|GroupsChanged|Renamed $event): void
    {
        $this->purger->addPurgeTags([
            "user_{$event->user->id}",
            "user_{$event->user->username}",
            'posts',
            'discussions',
            // TODO: If user has a lot of discussions chunk it and push to the queue job
            ...array_map(
                fn ($id) => "discussion_$id",
                $event->user->posts()->pluck('discussion_id')->toArray(),
            ),
        ]);
    }
}
