<?php

namespace ACPL\FlarumLSCache\Utility;

use ACPL\FlarumLSCache\Event\LSCachePurging;
use ACPL\FlarumLSCache\Job\PurgeCacheViaCliJob;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Events\Dispatcher;

use const PHP_SAPI;

/**
 * Manages the purging of LSCache by collecting purge data.
 * This data can be used either in middleware for HTTP requests or executed directly for CLI commands.
 */
class LSCachePurger
{
    /**
     * @var array{
     *   paths: string[],
     *   tags: string[]
     *  } $purgeData
     */
    private static array $purgeData = [
        'paths' => [],
        'tags' => [],
    ];

    /**
     * @var array|string[]
     */
    public static array $resourcesSupportedByEvent = ['discussion', 'post', 'user'];

    public function __construct(protected Dispatcher $events, protected Queue $queue)
    {
    }

    public function addPurgePath(string $purgePath): void
    {
        self::$purgeData['paths'][] = $purgePath;
    }

    /**
     * @param  array<string>  $paths
     */
    public function addPurgePaths(array $paths): void
    {
        self::$purgeData['paths'] = array_merge(self::$purgeData['paths'] ?? [], $paths);
    }

    public function addPurgeTag(string $tag): void
    {
        self::$purgeData['tags'][] = $tag;
    }

    /**
     * @param  array<string>  $tags
     */
    public function addPurgeTags(array $tags): void
    {
        self::$purgeData['tags'] = array_merge(self::$purgeData['tags'] ?? [], $tags);
    }

    public function getPurgeData(): array
    {
        return self::$purgeData;
    }

    public function clearPurgeData(): void
    {
        self::$purgeData = [
            'paths' => [],
            'tags' => [],
        ];
    }

    public function executePurge(): void
    {
        if (empty(self::$purgeData) || (empty(self::$purgeData['paths']) && empty(self::$purgeData['tags']))) {
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            $this->events->dispatch(new LSCachePurging(self::$purgeData));
            $this->clearPurgeData();
            $this->queue->push(new PurgeCacheViaCliJob(self::$purgeData));
        } // else purge will be handled by middleware
    }

    public static function isResourceSupportedByEvent(string $resource): bool
    {
        return in_array($resource, self::$resourcesSupportedByEvent);
    }
}
