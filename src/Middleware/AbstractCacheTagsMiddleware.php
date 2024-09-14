<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCacheHeader;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

abstract class AbstractCacheTagsMiddleware implements MiddlewareInterface
{
    protected string $currentRouteName;

    abstract protected function processTags(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response,
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
        if ($response->hasHeader(LSCacheHeader::TAG)) {
            $newTags = array_merge(
                explode(',', $response->getHeaderLine(LSCacheHeader::TAG)),
                $newTags,
            );
        }

        return $response->withHeader(LSCacheHeader::TAG, implode(',', array_unique($newTags)));
    }
}
