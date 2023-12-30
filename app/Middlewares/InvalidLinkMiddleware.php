<?php

namespace App\Middlewares;

use App\Exceptions\InvalidLinkException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class InvalidLinkMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Twig                     $twig,
        private readonly ResponseFactoryInterface $responseFactory,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidLinkException $e) {
            $response = $this->responseFactory->createResponse($e->getCode());

            return $this->twig->render($response, 'errors/invalid-link.twig', [
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}