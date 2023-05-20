<?php

namespace ACPL\FlarumCache\Listener;

use ACPL\FlarumCache\Command\LSCacheClearCommand;
use ACPL\FlarumCache\Utility\HtaccessManager;
use Flarum\Settings\Event\Saved;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class UpdateSettings
{
    protected HtaccessManager $htaccessManager;
    protected LSCacheClearCommand $cacheClearCommand;

    public function __construct(HtaccessManager $htaccessManager, LSCacheClearCommand $command)
    {
        $this->htaccessManager = $htaccessManager;
        $this->cacheClearCommand = $command;
    }

    /**
     * @throws FileNotFoundException|ExceptionInterface
     */
    public function handle(Saved $event): void
    {
        if (isset($event->settings['acpl-lscache.drop_qs'])) {
            $this->htaccessManager->updateHtaccess();
        }

        // If the LSCache is being disabled, initiate a cache clear operation.
        if (isset($event->settings['acpl-lscache.cache_enabled']) && ! $event->settings['acpl-lscache.cache_enabled']) {
            $this->cacheClearCommand->run(new ArrayInput([]), new NullOutput());
        }
    }
}
