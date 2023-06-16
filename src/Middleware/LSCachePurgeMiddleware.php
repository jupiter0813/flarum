<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\Abstract\PurgeMiddleware;
use ACPL\FlarumCache\LSCache;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Discussion\Discussion;
use Flarum\Http\RequestUtil;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSCachePurgeMiddleware extends PurgeMiddleware
{
    protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface {
        $routeName = $this->currentRouteName;

        $purgeParams = [];

        $params = $this->getRouteParams($request);

        $isDiscussion = $this->isDiscussion;
        $isPost = $this->isPost;

        $body = $request->getParsedBody();

        if ($isDiscussion || $isPost) {
            $purgeList = $this->settings->get('acpl-lscache.purge_on_discussion_update');
            if (! empty($purgeList)) {
                $purgeList = explode("\n", $purgeList);
                // Get only valid items
                $purgeList = array_filter($purgeList, fn($item) => Str::startsWith($item, ['/', 'tag=']));
                $purgeParams = array_merge($purgeParams, $purgeList);
            }

            // If this is a post update, we don't need to clear the home page cache unless the post is hidden
            $isPostUpdate = $routeName === 'posts.update';
            if (($isPostUpdate && Arr::has($body, 'data.attributes.isHidden')) || ! $isPostUpdate) {
                array_push($purgeParams, 'tag=default', 'tag=index', 'tag=discussions.index');
            }
        }

        if ($isPost) {
            $discussionId = Arr::get($body, 'data.relationships.discussion.data.id');

            if (! $discussionId) {
                // When an existing post is edited or deleted
                $postId = Arr::get($body, 'data.id');

                if ($postId) {
                    $discussionId = Post::find($postId)->discussion_id;
                }
            }

            if ($discussionId) {
                array_push($purgeParams, "tag=discussions$discussionId", "tag=discussion$discussionId");
            }
        }

        if (Str::endsWith($routeName, ['.create', '.update', '.delete'])) {
            $rootRouteName = LSCache::extractRootRouteName($routeName);

            // discussions.index is handled earlier
            if (! $isDiscussion) {
                $purgeParams[] = "tag=$rootRouteName.index";
            }

            if (! empty($params) && ! empty($params['id'])) {
                $purgeParams[] = "tag=$rootRouteName{$params['id']}";
            }
        }

        return $this->addPurgeParamsToResponse($response, $purgeParams);
    }
}
