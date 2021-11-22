<?php

namespace ACPL\FlarumCache\Api\Controller;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Http\RequestUtil;
use Flarum\User\Exception\PermissionDeniedException;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PurgeLSCacheController implements RequestHandlerInterface
{
    /**
     * @throws PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        RequestUtil::getActor($request)->assertAdmin();

        $response = new EmptyResponse();
        return $response->withHeader(LSCacheHeadersEnum::PURGE, '*');
    }
}
