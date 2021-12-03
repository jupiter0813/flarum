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

class LSCacheMiddleware implements MiddlewareInterface
{
    private SettingsRepositoryInterface $settings;
    /**
     * @var mixed
     */
    private $config;

    public function __construct(SettingsRepositoryInterface $settings, ConfigRepository $config)
    {
        $this->settings = $settings;
        $this->config = $config->get('session');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $method = $request->getMethod();

        if (!in_array($method, ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeadersEnum::CACHE_CONTROL)) {
            return $response;
        }

        $routeName = $request->getAttribute('routeName');

        if (Str::startsWith($routeName, ['auth', 'fof-oauth'])) {
            return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, 'no-cache');
        }

        if ($routeName === 'lscache.csrf') {
            $sessionTTL = $this->config['lifetime'] * 60;
            return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, "private,max-age=$sessionTTL");
        }

        $lscacheParams = [];

        //Guest only cache for now
        $user = RequestUtil::getActor($request);
        if ($user->isGuest()) {
            array_push($lscacheParams, 'public');

            $publicTTL = $this->settings->get('acpl-lscache.public_cache_ttl') ?: 300;
            array_push($lscacheParams, "max-age=$publicTTL");
        } else {
            array_push($lscacheParams, 'no-cache');
        }

        //TODO user group cache vary https://docs.litespeedtech.com/lscache/devguide/#cache-vary
        //TODO private cache

        return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, implode(',', $lscacheParams));
    }
}
