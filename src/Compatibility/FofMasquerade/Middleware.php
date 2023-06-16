<?php

namespace ACPL\FlarumCache\Compatibility\FofMasquerade;

use ACPL\FlarumCache\Abstract\PurgeMiddleware;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware extends PurgeMiddleware
{
    protected function processPurge(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ResponseInterface $response
    ): ResponseInterface {
        // Purge user profile cache when updating FriendsOfFlarum/masquerade fields
        if ($this->currentRouteName === 'masquerade.api.configure.save') {
            $userID = $this->getRouteParams($request)['id'];
            $user = User::find($userID);

            return $this->addPurgeParamsToResponse(
                $response,
                [
                    "tag=user_$user->id",
                    "tag=users_$user->id",
                    "tag=user_$user->username",
                    "tag=users_$user->username",
                    "tag=masquerade_$user->id",
                ]
            );
        }

        return $response;
    }
}
