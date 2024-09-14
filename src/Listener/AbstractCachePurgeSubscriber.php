<?php

namespace ACPL\FlarumLSCache\Listener;

use ACPL\FlarumLSCache\Event\LSCachePurging;
use ACPL\FlarumLSCache\Utility\LSCachePurger;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

abstract class AbstractCachePurgeSubscriber
{
    public function __construct(protected LSCachePurger $purger, protected SettingsRepositoryInterface $settings)
    {
    }

    abstract public function subscribe(Dispatcher $events): void;

    protected function addPurgeListener(Dispatcher $events, string $event, callable $handler): void
    {
        $events->listen($event, function ($eventInstance) use ($handler) {
            $handler($eventInstance);

            // Prevent infinite loop when something listens to LSCachePurging event
            if (! $eventInstance instanceof LSCachePurging) {
                $this->purger->executePurge();
            }
        });
    }
}
