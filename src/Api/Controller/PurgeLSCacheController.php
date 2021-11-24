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
        RequestUtil::getActor($request)->assertAdmin();

        $response = new EmptyResponse();

        $stale = $this->settings->get('acpl-lscache.serve_stale') ? 'stale,' : '';
        return $response->withHeader(LSCacheHeadersEnum::PURGE, "$stale*");
    }
}
