<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSTagsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return $response;
        }

        $routeName = $request->getAttribute('routeName');

        $params = $request->getAttribute('routeParameters');

        $tagParams = [$routeName];

        if (! empty($params)) {
            $rootRouteName = LSCache::extractRootRouteName($routeName);

            // Discussion
            if (! empty($params['id'])) {
                // The id parameter contains the slug. We only need id (int)
                $id = explode('-', $params['id'], 2)[0];
                if (! empty($id)) {
                    $tagParams[] = "{$rootRouteName}_$id";
                }
            }

            // User profile
            if (! empty($params['username'])) {
                $tagParams[] = "{$rootRouteName}_{$params['username']}";
            }

            // Slugs, eg. tag slug
            if (! empty($params['slug'])) {
                $tagParams[] = "{$rootRouteName}_{$params['slug']}";
            }
        }

        if ($response->hasHeader(LSCacheHeadersEnum::TAG)) {
            $tagParams = array_merge(
                explode(',', $response->getHeaderLine(LSCacheHeadersEnum::TAG)),
                $tagParams
            );
        }

        $tagParams = array_unique($tagParams);

        return $response->withHeader(LSCacheHeadersEnum::TAG, implode(',', $tagParams));
    }
}
