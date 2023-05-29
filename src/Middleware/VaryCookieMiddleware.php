<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VaryCookieMiddleware implements MiddlewareInterface
{
    private CookieFactory $cookie;
    private array $session;

    public function __construct(CookieFactory $cookie, ConfigRepository $config)
    {
        $this->cookie = $cookie;
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
            LSCacheHeadersEnum::VARY,
            "cookie={$this->cookie->getName(LSCache::VARY_COOKIE)},cookie={$this->cookie->getName('remember')},cookie=locale",
        );

        $user = RequestUtil::getActor($request);

        if ($user->isGuest() || $request->getAttribute('routeName') === 'logout') {
            return $response;
        }

        return $this->withVaryCookie($response, $session);
    }

    private function withVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::set(
            $response,
            $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->session['lifetime'] * 60)
        );
    }
}
