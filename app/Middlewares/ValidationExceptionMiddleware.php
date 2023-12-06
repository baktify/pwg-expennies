<?php

namespace App\Middlewares;

use App\Contracts\SessionInterface;
use App\Exceptions\ValidationException;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly SessionInterface $session,
        private readonly RequestService $requestService,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $oldData = $request->getParsedBody();
            $referer = $this->requestService->getReferer($request);
            $sensitiveKeys = array_flip(['password', 'confirmPassword']);

            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', array_diff_key($oldData, $sensitiveKeys));

            return $this->responseFactory
                ->createResponse()
                ->withHeader('Location', $referer)
                ->withStatus(302);
        }
    }
}