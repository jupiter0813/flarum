<?php
namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Flarum\Http\UrlGenerator;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginMiddleware implements MiddlewareInterface
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
        $loginUri = new Uri($this->url->to('forum')->path('/login'));

        $response = $handler->handle($request);

        if ($request->getUri()->getPath() === $loginUri->getPath() && $request->getMethod() === 'POST' && $response->getStatusCode() === 200) {
            return $this->withVaryCookie($response, $request->getAttribute('session'));
        }

        return $response;
    }

    private function withVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::set($response, $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->config['lifetime'] * 60));
    }
}
