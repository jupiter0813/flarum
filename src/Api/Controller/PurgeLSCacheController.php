<?php

namespace ACPL\FlarumCache\Api\Controller;

use ACPL\FlarumCache\LSCacheHeadersEnum;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PurgeLSCacheController implements RequestHandlerInterface
{
    private SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @throws PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $canPurge = false;

        if (RequestUtil::getActor($request)->isAdmin()) {
            $canPurge = true;
        }

        //If a command is used, use the temporary key because the user is not logged in
        if (! $canPurge) {
            $key = $this->settings->get('acpl-lscache.purgeKey');
            $reqKey = $request->getHeaderLine('LSCachePurgeKey');
            if (! empty($key) && ! empty($reqKey) && $key === $reqKey) {
                $canPurge = true;
            }
        }

        if (! $canPurge) {
            throw new PermissionDeniedException();
        }

        $response = new EmptyResponse();

        $stale = $this->settings->get('acpl-lscache.serve_stale') ? 'stale,' : '';

        return $response->withHeader(LSCacheHeadersEnum::PURGE, "$stale*");
    }
}
