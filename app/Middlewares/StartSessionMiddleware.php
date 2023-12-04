<?php

namespace App\Middlewares;

use App\Exceptions\SessionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session is already started');
        }

        if (headers_sent()) {
            throw new SessionException('Can not start sessions, headers are already sent');
        }

        session_set_cookie_params(['HttpOnly' => true, 'SameSite' => 'Lax']);
        session_start();

        $response = $handler->handle($request);

        session_write_close();

        return $response;
    }
}