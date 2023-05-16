<?php

namespace ACPL\FlarumCache\Listener;

use ACPL\FlarumCache\Utility\HtaccessManager;
use Flarum\Settings\Event\Saved;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class UpdateHtaccess
{
    protected HtaccessManager $htaccessManager;

    public function __construct(HtaccessManager $htaccessManager)
    {
        $this->htaccessManager = $htaccessManager;
    }

    /**
     * @throws FileNotFoundException
     */
    public function handle(Saved $event): void
    {
        if (isset($event->settings['acpl-lscache.drop_qs'])) {
            $this->htaccessManager->updateHtaccess();
        }
    }
}
