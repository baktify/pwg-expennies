<?php

namespace App\Middlewares;

use App\Contracts\SessionInterface;
use App\Exceptions\ValidationException;
use App\ResponseFormatter;
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
        private readonly SessionInterface         $session,
        private readonly RequestService           $requestService,
        private readonly ResponseFormatter        $responseFormatter,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse();

            if ($this->requestService->isXhr($request)) {
                return $this->responseFormatter->asJson(
                    $response->withStatus(422),
                    $e->errors
                );
            }

            $oldData = $request->getParsedBody();
            $referer = $this->requestService->getReferer($request);

            $sensitiveKeys = array_flip(['password', 'confirmPassword']);
            $oldDataFiltered = array_diff_key($oldData, $sensitiveKeys);

            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $oldDataFiltered);

            return $response->withHeader('Location', $referer)->withStatus(302);
        }
    }
}