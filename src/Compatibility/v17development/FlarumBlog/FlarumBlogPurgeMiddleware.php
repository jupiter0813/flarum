<?php

namespace ACPL\FlarumCache\Compatibility\v17development\FlarumBlog;

use ACPL\FlarumCache\Abstract\PurgeMiddleware;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use V17Development\FlarumBlog\BlogMeta\BlogMeta;

class FlarumBlogPurgeMiddleware extends PurgeMiddleware
{
    protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface {
        $isDiscussion = $this->isDiscussion;
        $isPost = $this->isPost;

        if (! ($isDiscussion || $isPost)) {
            return $response;
        }

        if ($this->currentRouteName === 'discussions.create') {
            $body = $request->getParsedBody();
            if (Arr::has($body, 'data.attributes.blogMeta')) {
                return $this->addPurgeParamsToResponse($response, ['tag=blog.overview']);
            }
        }

        $currentPurgeParams = $response->getHeaderLine(LSCacheHeadersEnum::PURGE);
        if (empty($currentPurgeParams)) {
            return $response;
        }

        $newPurgeParams = [];
        $currentPurgeParams = explode(',', $currentPurgeParams);

        // Blog extension is using default Flarum discussion api routes, so we can just reuse previous middleware to get the blog post id
        $discussionParam = Arr::first(
            $currentPurgeParams,
            fn (string $param) => Str::startsWith($param, ['tag=discussion_', 'tag=discussions_'])
        );
        if (empty($discussionParam)) {
            return $response;
        }

        if (preg_match('/(\d+)/', $discussionParam, $matches)) {
            $discussionId = $matches[1];
            $newPurgeParams[] = 'tag=blog_'.$discussionId;

            // If the previous response wants to purge the index page and this is a blog post, we need to purge the blog overview page as well
            if (
                in_array('tag=index', $currentPurgeParams)
                && BlogMeta::where('discussion_id', '=', $discussionId)->first()
            ) {
                $newPurgeParams[] = 'tag=blog.overview';
            }
        }

        return $this->addPurgeParamsToResponse($response, $newPurgeParams);
    }
}
