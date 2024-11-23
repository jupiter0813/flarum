<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCacheHeader;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class StatusCodesCacheMiddleware implements MiddlewareInterface
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeader::CACHE_CONTROL)) {
            return $response;
        }

        $actor = RequestUtil::getActor($request);
        // Guest-only cache
        if (! $actor->isGuest()) {
            return $response;
        }

        $settings = $this->settings->get('acpl-lscache.status_codes_cache');
        $lines = array_filter(explode("\n", $settings));
        if (empty($lines)) {
            return $response;
        }

        $statusCode = $response->getStatusCode();

        foreach ($lines as $line) {
            // [0] - status code, [1] - cache ttl
            $codeTtl = array_map('intval', explode(' ', trim($line)));
            if (empty($codeTtl[0]) || empty($codeTtl[1])) {
                continue;
            }

            if ($codeTtl[0] === $statusCode) {
                return $response->withHeader(LSCacheHeader::CACHE_CONTROL, "public,max-age=$codeTtl[1]");
            }
        }

        return $response;
    }
}
