<?php

namespace ACPL\FlarumLSCache\Middleware;

use ACPL\FlarumLSCache\LSCache;
use Dflydev\FigCookies\FigResponseCookies;
use Flarum\Http\CookieFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\{ResponseInterface, ResponseInterface as Response, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class LoginMiddleware implements MiddlewareInterface
{
    private array $session;

    public function __construct(protected CookieFactory $cookie, ConfigRepository $config)
    {
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
            $this->cookie->make(LSCache::VARY_COOKIE, $session->token(), $this->session['lifetime'] * 60),
        );
    }
}
