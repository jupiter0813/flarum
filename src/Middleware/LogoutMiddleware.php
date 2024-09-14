<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCache;
use ACPL\FlarumLSCache\LSCacheHeader;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Illuminate\Contracts\Session\Session;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\{ResponseInterface, ResponseInterface as Response, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class LogoutMiddleware implements MiddlewareInterface
{
    public function __construct(protected CookieFactory $cookie)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->getAttribute('routeName') === 'logout' && $response instanceof RedirectResponse) {
            $response = $response->withHeader(LSCacheHeader::CACHE_CONTROL, 'no-cache');

            return $this->withExpiredVaryCookie($response, $request->getAttribute('session'));
        }

        return $response;
    }

    private function withExpiredVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::set(
            FigResponseCookies::remove($response, LSCache::VARY_COOKIE),
            $this->cookie->make(LSCache::VARY_COOKIE, $session->token())->expire(),
        );
    }
}
