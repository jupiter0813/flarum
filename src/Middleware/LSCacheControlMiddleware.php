<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSCacheControlMiddleware implements MiddlewareInterface
{
    private SettingsRepositoryInterface $settings;
    private array $session;

    public function __construct(SettingsRepositoryInterface $settings, ConfigRepository $config)
    {
        $this->settings = $settings;
        $this->session = $config->get('session');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $method = $request->getMethod();

        if (! in_array($method, ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeadersEnum::CACHE_CONTROL)) {
            return $response;
        }

        $routeName = $request->getAttribute('routeName');
        //Exclude FriendsOfFlarum/OAuth routes
        if (Str::startsWith($routeName, ['auth', 'fof-oauth'])) {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        //Exclude paths specified in settings
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

        //Exclude purge API route
        if ($routeName === 'lscache.purge') {
            return $this->withCacheControlHeader($response, 'no-cache');
        }

        //Cache CSRF privately
        if ($routeName === 'lscache.csrf') {
            $sessionTTL = $this->session['lifetime'] * 60;

            return $this->withCacheControlHeader($response, "private,max-age=$sessionTTL");
        }

        $lscacheParams = [];

        //Guest only cache for now
        $user = RequestUtil::getActor($request);
        if ($user->isGuest()) {
            $lscacheParams[] = 'public';

            $publicTTL = $this->settings->get('acpl-lscache.public_cache_ttl') ?: 604_800;
            $lscacheParams[] = "max-age=$publicTTL";
        } else {
            $lscacheParams[] = 'no-cache';
        }

        //TODO user group cache vary https://docs.litespeedtech.com/lscache/devguide/#cache-vary
        //TODO private cache

        return $this->withCacheControlHeader($response, implode(',', $lscacheParams));
    }

    private function withCacheControlHeader(ResponseInterface $response, string $paramsStr): ResponseInterface
    {
        return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, $paramsStr);
    }
}
