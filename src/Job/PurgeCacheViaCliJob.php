<?php

namespace ACPL\FlarumLSCache\Job;

use ACPL\FlarumLSCache\Command\LSCachePurgeCommand;
use Flarum\Queue\AbstractJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Console\{Input\ArrayInput, Output\NullOutput};

class PurgeCacheViaCliJob extends AbstractJob implements ShouldQueue
{
    /**
     * @param  array{
     *   paths: string[],
     *   tags: string[]
     *  }  $data
     */
    public function __construct(protected array $data = ['paths' => [], 'tags' => []])
    {
    }

    public function handle(LSCachePurgeCommand $command): void
    {
        $input = [];

        if (! empty($this->data['paths'])) {
            $input['--path'] = $this->data['paths'];
        }

        if (! empty($this->data['tags'])) {
            $input['--tag'] = $this->data['tags'];
        }

        $command->run(new ArrayInput($input), new NullOutput());
    }
}
