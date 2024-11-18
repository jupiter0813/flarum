<?php

namespace ACPL\FlarumLSCache\Compatibility\FoF;

use ACPL\FlarumLSCache\Listener\{
    AbstractCachePurgeSubscriber,
    DiscussionCachePurgeTrait
};
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use Illuminate\Contracts\Events\Dispatcher;

class MergeDiscussionsEventSubscriber extends AbstractCachePurgeSubscriber
{
    use DiscussionCachePurgeTrait;

    public function subscribe(Dispatcher $events): void
    {
        $this->addPurgeListener($events, DiscussionWasMerged::class, [$this, 'handleDiscussionWasMerged']);
    }

    protected function handleDiscussionWasMerged(DiscussionWasMerged $event): void
    {
        $this->handleDiscussionRelatedPurge();

        $discussions = $event->mergedDiscussions;
        $discussions->each(fn (Discussion $discussion) => $this->purger->addPurgeTag("discussion_$discussion->id"));

        $event->posts->each(function (Post $post) {
            $this->purger->addPurgeTags([
                "post_$post->id",
                "user_$post->user_id",
                "user_{$post->user->username}",
            ]);
        });
    }
}
