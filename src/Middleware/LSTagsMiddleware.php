<?php
namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use ACPL\FlarumCache\LSCache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LSTagsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!in_array($request->getMethod(), ['GET', 'HEAD']) || $response->hasHeader(LSCacheHeadersEnum::TAG)) {
            return $response;
        }

        $routeName = $request->getAttribute('routeName');
        $rootRouteName = LSCache::extractRootRouteName($routeName);

        $params = $request->getAttribute('routeParameters');

        $lsTagsString = $routeName;

        if (!empty($params) && !empty($params['id'])) {
            // The id parameter contains the slug. We only need the id (int)
            $id = explode('-', $params['id'], 2)[0];
            $lsTagsString .= ",$rootRouteName" . $id ?: $params['id'];
        }

        return $response->withHeader(LSCacheHeadersEnum::TAG, $lsTagsString);
    }
}
