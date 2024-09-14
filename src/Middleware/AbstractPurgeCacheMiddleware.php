<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\Event\LSCachePurging;
use ACPL\FlarumLSCache\LSCacheHeader;
use ACPL\FlarumLSCache\Utility\LSCachePurger;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Events\Dispatcher;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

abstract class AbstractPurgeCacheMiddleware implements MiddlewareInterface
{
    protected string $currentRouteName;

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected LSCachePurger $cachePurger,
        protected Dispatcher $events,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->currentRouteName = $request->getAttribute('routeName');

        if ($this->shouldProcessPurge($request, $response)) {
            $this->preparePurgeData($request);
            $this->dispatchLSCachePurgingEvent($request);
            $response = $this->addPurgeParamsToResponse($response);
        }

        return $response;
    }

    protected function shouldProcessPurge(ServerRequestInterface $request, ResponseInterface $response): bool
    {
        return in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && $response->getStatusCode() < 400;
    }

    abstract protected function preparePurgeData(ServerRequestInterface $request): void;

    protected function addPurgeParamsToResponse(ResponseInterface $response): ResponseInterface
    {
        $purgeData = $this->cachePurger->getPurgeData();
        $newPurgeParams = $this->formatPurgeParams($purgeData);

        if ($response->hasHeader(LSCacheHeader::PURGE)) {
            $existingPurgeParams = explode(',', $response->getHeaderLine(LSCacheHeader::PURGE));
            $newPurgeParams = array_merge($existingPurgeParams, $newPurgeParams);
        }

        if (empty($newPurgeParams)) {
            return $response;
        }

        $this->addStaleParamIfNeeded($newPurgeParams);
        $this->cachePurger->clearPurgeData();

        return $response->withHeader(LSCacheHeader::PURGE, implode(',', array_unique($newPurgeParams)));
    }

    protected function formatPurgeParams(array $purgeData): array
    {
        $params = $purgeData['paths'] ?? [];
        if (! empty($purgeData['tags'])) {
            $params = array_merge(
                $params,
                array_map(fn (string $tag) => "tag=$tag", $purgeData['tags']),
            );
        }

        return $params;
    }

    protected function addStaleParamIfNeeded(array &$params): void
    {
        if ($this->settings->get('acpl-lscache.serve_stale') && ! in_array('stale', $params)) {
            array_unshift($params, 'stale');
        }
    }

    protected function getRouteParams(ServerRequestInterface $request): array
    {
        return $request->getAttribute('routeParameters');
    }

    protected function dispatchLSCachePurgingEvent(ServerRequestInterface $request): void
    {
        $purgeData = $this->cachePurger->getPurgeData();
        $this->events->dispatch(new LSCachePurging($purgeData), RequestUtil::getActor($request));
    }
}
