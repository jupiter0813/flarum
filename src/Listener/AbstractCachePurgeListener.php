<?php

namespace ACPL\FlarumLSCache\Listener;

use ACPL\FlarumLSCache\Utility\LSCachePurger;
use Flarum\Settings\SettingsRepositoryInterface;

abstract class AbstractCachePurgeListener
{
    public function __construct(protected LSCachePurger $purger, protected SettingsRepositoryInterface $settings)
    {
    }

    public function handle($event): void
    {
        $this->addPurgeData($event);
        $this->purger->executePurge();
    }

    abstract protected function addPurgeData($event): void;
}
