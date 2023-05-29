<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Dflydev\FigCookies\FigResponseCookies;
use Illuminate\Contracts\Session\Session;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogoutMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->getAttribute('routeName') === 'logout' && $response instanceof RedirectResponse) {
            $response = $response->withHeader(LSCacheHeadersEnum::CACHE_CONTROL, 'no-cache');

            return $this->withExpiredVaryCookie($response, $request->getAttribute('session'));
        }

        return $response;
    }

    private function withExpiredVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::expire($response, LSCache::VARY_COOKIE);
    }
}
