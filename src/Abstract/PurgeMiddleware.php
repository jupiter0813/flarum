<?php

namespace ACPL\FlarumCache\Abstract;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class PurgeMiddleware implements MiddlewareInterface
{
    protected SettingsRepositoryInterface $settings;

    protected string $currentRouteName;
    protected bool $isDiscussion;
    protected bool $isPost;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (
            ! in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            || $response->getStatusCode() >= 400
        ) {
            return $response;
        }

        $this->currentRouteName = $request->getAttribute('routeName');
        $this->isDiscussion = str_starts_with($this->currentRouteName, 'discussions');
        $this->isPost = str_starts_with($this->currentRouteName, 'posts');

        // If this is just an update of the last read post, there is no point in clearing the public cache
        if ($this->isDiscussion && Arr::get(
                $request->getParsedBody(),
                'data.attributes.lastReadPostNumber')
        ) {
            return $response;
        }


        return $this->processPurge($request, $handler, $response);
    }

    abstract protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface;


    protected function addPurgeParamsToResponse(ResponseInterface $response, array $newPurgeParams): ResponseInterface
    {
        if ($response->hasHeader(LSCacheHeadersEnum::PURGE)) {
            $existingPurgeParams = explode(',', $response->getHeaderLine(LSCacheHeadersEnum::PURGE));
            $newPurgeParams = array_unique(array_merge($existingPurgeParams, $newPurgeParams));
        }

        if (count($newPurgeParams) < 1) {
            return $response;
        }

        if ($this->settings->get('acpl-lscache.serve_stale') && ! array_key_exists('stale', $newPurgeParams)) {
            array_unshift($newPurgeParams, 'stale');
        }

        return $response->withHeader(LSCacheHeadersEnum::PURGE, implode(',', $newPurgeParams));
    }

    protected function getRouteParams($request): array
    {
        return $request->getAttribute('routeParameters');
    }
}
