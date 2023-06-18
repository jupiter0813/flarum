<?php

namespace ACPL\FlarumCache\Compatibility\FlarumTags;

use ACPL\FlarumCache\Abstract\PurgeMiddleware;
use Flarum\Discussion\Discussion;
use Flarum\Http\UrlGenerator;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\Tag;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlarumTagsPurgeMiddleware extends PurgeMiddleware
{
    protected UrlGenerator $url;

    public function __construct(SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->url = $url;
        parent::__construct($settings);
    }

    protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface {
        $isDiscussion = $this->isDiscussion;
        $isPost = $this->isPost;

        if (! $isDiscussion && ! $isPost) {
            return $response;
        }

        $body = $request->getParsedBody();
        $routeName = $this->currentRouteName;

        // When a post is edited, there is no need to purge tags cache unless the post is being hidden
        if ($routeName === 'posts.update' && ! Arr::has($body, 'data.attributes.isHidden')) {
            return $response;
        }

        $response->getBody()->rewind();
        $payload = $response->getBody()->getContents();
        $payload = json_decode($payload, true);

        if ($isDiscussion) {
            $discussionId = Arr::get($payload, 'data.id');
        } else {
            $discussionId = Arr::get($payload, 'data.relationships.discussion.data.id');
            if (! $discussionId) {
                $postId = Arr::get($payload, 'data.id');
                $discussionId = Post::find($postId)->discussion_id;
            }
        }

        $discussion = Discussion::find($discussionId);
        if (! $discussion) {
            return $response;
        }

        /**
         * @var Tag[] $tags
         * @phpstan-ignore-next-line
         */
        $tags = $discussion->tags;

        if (! $tags) {
            return $response;
        }

        $purgeParams = ['tags.index', 'tags'];

        foreach ($tags as $tag) {
            $purgeParams[] = "tag_$tag->slug";
        }

        return $this->addPurgeParamsToResponse($response, $purgeParams);
    }
}
