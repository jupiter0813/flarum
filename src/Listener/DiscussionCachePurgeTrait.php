<?php

namespace ACPL\FlarumLSCache\Listener;

use ACPL\FlarumLSCache\Utility\LSCachePurger;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;

trait DiscussionCachePurgeTrait
{
    protected SettingsRepositoryInterface $settings;
    protected LSCachePurger $purger;

    protected function handleDiscussionRelatedPurge(): void
    {
        $this->purger->addPurgeTags([
            'default',
            'index',
            'discussions',
        ]);

        $purgeList = $this->settings->get('acpl-lscache.purge_on_discussion_update');
        if (! empty($purgeList)) {
            $purgeList = explode("\n", $purgeList);

            $paths = Arr::where($purgeList, fn ($item) => str_starts_with($item, '/'));
            if (! empty($paths)) {
                $this->purger->addPurgePaths($paths);
            }

            $tags = Arr::where($purgeList, fn ($item) => str_starts_with($item, 'tag='));
            if (! empty($tags)) {
                $this->purger->addPurgeTags($tags);
            }
        }
    }
}
