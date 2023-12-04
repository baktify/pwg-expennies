<?php

namespace App\Middlewares;

use App\Exceptions\ValidationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $oldData = $request->getParsedBody();
            $sensitiveKeys = array_flip(['password', 'confirmPassword']);

            $_SESSION['old'] = array_diff_key($oldData, $sensitiveKeys);

            $_SESSION['errors'] = $e->errors;

            $referer = $request->getServerParams()['HTTP_REFERER'];

            $response = $this->responseFactory->createResponse();

            return $response
                ->withHeader('Location', $referer)
                ->withStatus(303);
        }
    }
}