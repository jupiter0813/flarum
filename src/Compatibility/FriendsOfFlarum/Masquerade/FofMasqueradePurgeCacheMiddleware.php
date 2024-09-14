<?php

namespace ACPL\FlarumLSCache\Compatibility\FriendsOfFlarum\Masquerade;

use ACPL\FlarumLSCache\Middleware\AbstractPurgeCacheMiddleware;
use Flarum\User\User;
use Psr\Http\Message\ServerRequestInterface;

class FofMasqueradePurgeCacheMiddleware extends AbstractPurgeCacheMiddleware
{
    protected function preparePurgeData(ServerRequestInterface $request): void
    {
        if ($this->currentRouteName === 'masquerade.api.configure.save') {
            $userID = $this->getRouteParams($request)['id'];
            $user = User::find($userID);

            if ($user) {
                $this->cachePurger->addPurgeTags([
                    "user_$user->id",
                    "user_$user->username",
                    "masquerade_$user->id",
                ]);
            }
        }
    }
}
