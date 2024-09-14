<?php

namespace ACPL\FlarumLSCache\Listener;

use Flarum\Foundation\Event\ClearingCache;

class ClearingCacheListener extends AbstractCachePurgeListener
{
    /** @param  ClearingCache  $event */
    protected function addPurgeData($event): void
    {
        if ($this->settings->get('acpl-lscache.clearing_cache_listener')) {
            $this->purger->addPurgePath('*');
        }
    }
}
