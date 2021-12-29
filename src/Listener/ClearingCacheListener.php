<?php

namespace ACPL\FlarumCache\Listener;

use ACPL\FlarumCache\Command\LSCacheClearCommand;
use Exception;
use Flarum\Settings\SettingsRepositoryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ClearingCacheListener
{
    private LSCacheClearCommand $command;
    private SettingsRepositoryInterface $settings;

    public function __construct(LSCacheClearCommand $command, SettingsRepositoryInterface $settings)
    {
        $this->command = $command;
        $this->settings = $settings;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        if ($this->settings->get('acpl-lscache.clearing_cache_listener')) {
            $this->command->run(new ArrayInput([]), new NullOutput());
        }
    }
}
