<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCache;
use ACPL\FlarumLSCache\LSCacheHeader;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class VaryCookieMiddleware implements MiddlewareInterface
{
    private array $session;

    public function __construct(protected CookieFactory $cookie, ConfigRepository $config)
    {
        $this->session = $config->get('session');
    }

    /**
     * @see https://docs.litespeedtech.com/lscache/devguide/advanced/#cache-varies
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $response = $handler->handle($request);

        $response = $response->withHeader(
            LSCacheHeader::VARY,
            "cookie={$this->cookie->getName(LSCache::VARY_COOKIE)},cookie={$this->cookie->getName('remember')},cookie=locale",
        );

        $user = RequestUtil::getActor($request);

        if ($user->isGuest() || $request->getAttribute('routeName') === 'logout') {
            return $response;
        }

        return $this->withVaryCookie($response, $session);
    }

    private function withVaryCookie(ResponseInterface $response, ?Session $session): ResponseInterface
    {
        if (! $session) {
            return $response;
        }

        return FigResponseCookies::set(
            $response,
            $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->session['lifetime'] * 60),
        );
    }
}
