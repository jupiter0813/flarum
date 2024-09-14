<?php

namespace ACPL\FlarumLSCache\Event;

use Flarum\User\User;

/**
 * The LSCache is going to be purged.
 */
class LSCachePurging
{
    /**
     * @param  array{
     *   paths: string[],
     *   tags: string[]
     *  }  $data
     */
    public function __construct(public array $data, public ?User $actor = null)
    {
    }
}
