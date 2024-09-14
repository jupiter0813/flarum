<?php

namespace ACPL\FlarumLSCache\Listener;

use ACPL\FlarumLSCache\Utility\HtaccessManager;
use ACPL\FlarumLSCache\Utility\LSCachePurger;
use Flarum\Settings\Event\Saved;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class UpdateSettingsListener
{
    public function __construct(protected HtaccessManager $htaccessManager, protected LSCachePurger $purger)
    {
    }

    /**
     * @throws FileNotFoundException
     */
    public function handle(Saved $event): void
    {
        if (isset($event->settings['acpl-lscache.drop_qs'])) {
            $this->htaccessManager->updateHtaccess();
        }

        // If the LSCache is being disabled, initiate a cache purge operation.
        if (isset($event->settings['acpl-lscache.cache_enabled']) && ! $event->settings['acpl-lscache.cache_enabled']) {
            $this->purger->addPurgePath('*');
            $this->purger->executePurge();
        }
    }
}
