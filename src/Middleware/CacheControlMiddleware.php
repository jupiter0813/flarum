<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCacheHeader;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class CacheControlMiddleware implements MiddlewareInterface
{
    private array $session;

    public function __construct(private readonly SettingsRepositoryInterface $settings, ConfigRepository $config)
    {
        $this->session = $config->get('session');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $method = $request->getMethod();

        if (! $this->settings->get('acpl-lscache.cache_enabled')) {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        if (! in_array($method, ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeader::CACHE_CONTROL)) {
            return $response;
        }

        $routeName = $request->getAttribute('routeName');

        // Exclude auth routes
        if (Str::startsWith($routeName, ['auth', 'fof-oauth']) || $routeName === 'resetPassword') {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        // Exclude paths specified in settings
        $excludedPaths = Str::of($this->settings->get('acpl-lscache.cache_exclude'));
        if ($excludedPaths->isNotEmpty()) {
            $excludedPathsArr = $excludedPaths->explode("\n");
            $currentPath = Str::of($request->getUri()->getPath());

            foreach ($excludedPathsArr as $pattern) {
                if (! empty(trim($pattern)) && $currentPath->test('/'.addcslashes($pattern, '/').'/')) {
                    return $this->withCacheControlHeader($response, 'no-cache');
                }
            }
        }

        // Exclude purge API route
        if ($routeName === 'lscache.purge') {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        // Cache CSRF privately
        if ($routeName === 'lscache.csrf') {
            // Subtract 2 minutes (120 seconds)
            // from the session lifetime to set the cache to expire before the actual session does.
            // This is to prevent a potential issue where an expired CSRF token might be served from the cache.
            return $this->withCacheControlHeader(
                $response,
                'private,max-age='.(($this->session['lifetime'] * 60) - 120),
            );
        }

        $lscacheParams = [];

        // Guest-only cache
        $user = RequestUtil::getActor($request);
        if ($user->isGuest()) {
            $lscacheParams[] = 'public';

            $publicTTL = $this->settings->get('acpl-lscache.public_cache_ttl') ?: 604_800;
            $lscacheParams[] = "max-age=$publicTTL";
        } else {
            $lscacheParams[] = 'no-cache';
        }

        return $this->withCacheControlHeader($response, implode(',', $lscacheParams));
    }

    private function withCacheControlHeader(ResponseInterface $response, string $paramsStr): ResponseInterface
    {
        return $response->withHeader(LSCacheHeader::CACHE_CONTROL, $paramsStr);
    }
}
