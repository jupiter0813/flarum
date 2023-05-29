<?php

namespace ACPL\FlarumCache\Middleware;

use ACPL\FlarumCache\LSCache;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginMiddleware implements MiddlewareInterface
{
    private CookieFactory $cookie;
    private array $session;

    public function __construct(CookieFactory $cookie, ConfigRepository $config)
    {
        $this->cookie = $cookie;
        $this->session = $config->get('session');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (
            $request->getAttribute('routeName') === 'login'
            && $request->getMethod() === 'POST'
            && $response->getStatusCode() === 200
        ) {
            return $this->withVaryCookie($response, $request->getAttribute('session'));
        }

        return $response;
    }

    private function withVaryCookie(Response $response, Session $session): Response
    {
        return FigResponseCookies::set(
            $response,
            $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->session['lifetime'] * 60)
        );
    }
}
