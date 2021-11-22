<?php
namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use ACPL\FlarumCache\LSCache;
use Flarum\Http\RequestUtil;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSCacheMiddleware implements MiddlewareInterface
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

        if (!in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE']) || $response->hasHeader(LSCacheHeadersEnum::CACHE_CONTROL)) {
            return $response;
        }

        $currentRoute = $request->getUri()->getPath();
        $routeName = $request->getAttribute('routeName');
        $params = $request->getAttribute('routeParameters');

        if ($routeName === 'lscache.csrf') {
            return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, 'no-cache');
        }

        //Purge cache
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if ($response->hasHeader(LSCacheHeadersEnum::PURGE)) {
                return $response;
            }

            $lscachePurgeString = [$currentRoute];

            if (Str::endsWith($routeName, ['.create', '.update', '.delete'])) {
                $rootRouteName = LSCache::extractRootRouteName($routeName);
                array_push($lscachePurgeString, "tag=$rootRouteName.index");

                if (!empty($params) && !empty($params['id'])) {
                    array_push($lscachePurgeString, "tag=$rootRouteName{$params['id']}");
                }
            }

            $isDiscussion = Str::startsWith($routeName, 'discussions');
            $isPost = Str::startsWith($routeName, 'posts');

            if ($isDiscussion || $isPost) {
                array_push($lscachePurgeString, 'tag=default', 'tag=index');

                $purgeList = $this->settings->get('acpl-lscache.purge_on_discussion_update');
                if (!empty($purgeList)) {
                    $purgeList = explode("\n", $purgeList);
                    $purgeList = array_filter($purgeList, fn($item) => Str::startsWith($item, ['/', 'tag=']));
                    $lscachePurgeString = array_merge($lscachePurgeString, $purgeList);
                }
            }

            if ($isPost) {
                $postId = Arr::get($request->getParsedBody(), 'data.id');
                if ($postId) {
                    $discussionId = Post::find($postId)->discussion_id;
                    array_push($lscachePurgeString, "tag=discussions$discussionId", "tag=discussion$discussionId");
                }
            }

            return $response->withHeader(LSCacheHeadersEnum::PURGE, implode(',', $lscachePurgeString));
        }

        $lscacheString = [];

        //Guest only cache for now
        $user = RequestUtil::getActor($request);
        if ($user->isGuest()) {
            array_push($lscacheString, 'public');

            $publicTtl = $this->settings->get('acpl-lscache.public_cache_ttl') ?: 300;
            array_push($lscacheString, "max-age=$publicTtl");
        } else {
            array_push($lscacheString, 'no-cache');
        }

        //TODO user group cache vary https://docs.litespeedtech.com/lscache/devguide/#cache-vary
        //TODO private cache

        return $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, implode(',', $lscacheString));
    }
}
