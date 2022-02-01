<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSCacheAdminCacheControlMiddleware implements MiddlewareInterface
{
    private SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $method = $request->getMethod();

        $cacheEnabled = $this->settings->get('acpl-lscache.admin_cache');
        if (!$cacheEnabled) {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        if (!in_array($method, ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeadersEnum::CACHE_CONTROL)) {
            return $response;
        }

        $ttl = $this->settings->get('acpl-lscache.admin_cache_ttl');

        return $this->withCacheControlHeader($response, 'private,max-age=' . !empty($ttl) ? $ttl : 300);
    }

    private function withCacheControlHeader(ResponseInterface $response, string $paramsStr): ResponseInterface
    {
        return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, $paramsStr);
    }
}
