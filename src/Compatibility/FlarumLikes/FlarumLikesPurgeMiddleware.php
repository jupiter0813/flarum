<?php

namespace ACPL\FlarumCache\Compatibility\FlarumLikes;

use ACPL\FlarumCache\Abstract\PurgeMiddleware;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FlarumLikesPurgeMiddleware extends PurgeMiddleware
{

    protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface {
        if ($this->currentRouteName !== 'posts.update') {
            return $response;
        }

        $requestBody = $request->getParsedBody();

        if (! Arr::has($requestBody, 'data.attributes.isLiked')) {
            return $response;
        }

        return $this->addPurgeParamsToResponse($response, ['users.index']);
    }
}
