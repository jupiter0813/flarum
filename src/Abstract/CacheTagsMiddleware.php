<?php

namespace ACPL\FlarumCache\Abstract;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class CacheTagsMiddleware implements MiddlewareInterface
{
    protected string $currentRouteName;

    abstract protected function processTags(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return $response;
        }

        $this->currentRouteName = $request->getAttribute('routeName');

        return $this->processTags($request, $handler, $response);
    }

    protected function addLSCacheTagsToResponse(ResponseInterface $response, array $newTags): ResponseInterface
    {
        if ($response->hasHeader(LSCacheHeadersEnum::TAG)) {
            $newTags = array_merge(
                explode(',', $response->getHeaderLine(LSCacheHeadersEnum::TAG)),
                $newTags
            );
        }

        $newTags = array_unique($newTags);

        return $response->withHeader(LSCacheHeadersEnum::TAG, implode(',', $newTags));
    }
}
