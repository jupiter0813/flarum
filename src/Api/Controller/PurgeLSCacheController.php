<?php

namespace ACPL\FlarumLSCache\Api\Controller;

use ACPL\FlarumLSCache\LSCacheHeader;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

class PurgeLSCacheController implements RequestHandlerInterface
{
    private SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @throws PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $canPurge = RequestUtil::getActor($request)->can('lscache.purge');

        // If a command is used, use the temporary key because the user is not logged in
        if (! $canPurge) {
            $key = $this->settings->get('acpl-lscache.purgeKey');
            $reqKey = $request->getHeaderLine('LSCachePurgeKey');
            if (! empty($key) && ! empty($reqKey) && $key === $reqKey) {
                $canPurge = true;
            }
        }

        if (! $canPurge) {
            throw new PermissionDeniedException();
        }

        $purgeParams = $this->settings->get('acpl-lscache.serve_stale') ? ['stale'] : [];

        $paths = Arr::get($request->getQueryParams(), 'paths');
        $tags = Arr::get($request->getQueryParams(), 'tags');

        if (empty($paths) && empty($tags)) {
            $purgeParams[] = '*';
        } else {
            if (! empty($paths)) {
                $purgeParams = array_merge($purgeParams, $paths);
            }
            if (! empty($tags)) {
                $purgeParams = array_merge(
                    $purgeParams,
                    array_map(fn ($tag) => "tag=$tag", $tags),
                );
            }
        }

        return (new EmptyResponse())
            ->withHeader(LSCacheHeader::PURGE, implode(',', $purgeParams))
            ->withHeader(LSCacheHeader::CACHE_CONTROL, 'no-cache');
    }
}
