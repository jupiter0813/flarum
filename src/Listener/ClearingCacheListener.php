<?php

namespace ACPL\FlarumCache\Listener;

use ACPL\FlarumCache\Command\LSCacheClearCommand;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ClearingCacheListener
{
    private LSCacheClearCommand $command;

    public function __construct(LSCacheClearCommand $command)
    {
        $this->command = $command;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $this->command->run(new ArrayInput([]), new NullOutput());
    }
}
