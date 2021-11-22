<?php
namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use ACPL\FlarumCache\LSCacheHeadersEnum;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VaryCookieMiddleware implements MiddlewareInterface
{
    private CookieFactory $cookie;
    private UrlGenerator $url;

    public function __construct(CookieFactory $cookie, UrlGenerator $url, ConfigRepository $config)
    {
        $this->cookie = $cookie;
        $this->url = $url;
        $this->config = $config->get('session');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $response = $handler->handle($request);

        $response = $response->withHeader(
            LSCacheHeadersEnum::VARY,
            "cookie={$this->cookie->getName(LSCache::VARY_COOKIE)},cookie={$this->cookie->getName('remember')}",
        );

        $user = RequestUtil::getActor($request);

        $logoutUri = new Uri($this->url->to('forum')->path('/logout'));
        if ($user->isGuest() || $request->getUri()->getPath() === $logoutUri->getPath()) {
            return $response;
        }

        return $this->withVaryCookie($response, $session);
    }

    private function withVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::set($response, $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->config['lifetime'] * 60));
    }
}
