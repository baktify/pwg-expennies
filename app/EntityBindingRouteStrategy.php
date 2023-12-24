<?php

namespace App;

use App\Contracts\EntityManagerServiceInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class EntityBindingRouteStrategy implements InvocationStrategyInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
//        private readonly ResponseFactoryInterface      $responseFactory,
    )
    {
    }

    public function __invoke(
        callable               $callable,
        ServerRequestInterface $request,
        ResponseInterface      $response,
        array                  $routeArguments
    ): ResponseInterface
    {
        $reflectionCallable = $this->createReflectionForCallable($callable);
        $resolvedArguments = [];

        foreach ($reflectionCallable->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                continue;
            }

            $typeName = $type->getName();
            $paramName = $parameter->getName();

            if ($type->isBuiltin()) {
                if ($typeName === 'array' && $paramName === 'args') {
                    $resolvedArguments[] = $routeArguments;
                }
            } else {
                if ($typeName === ServerRequestInterface::class) {
                    $resolvedArguments[] = $request;
                } else if ($typeName === ResponseInterface::class) {
                    $resolvedArguments[] = $response;
                } else {
                    $entityId = $routeArguments[$paramName] ?? null;

                    if (!$entityId || $parameter->allowsNull()) {
                        throw new InvalidArgumentException('Unable to resolve ' . $paramName . ' argument');
                    }

                    $entity = $this->entityManager->getRepository($typeName)->find($entityId);

                    if (!$entity) {
                        return $response->withStatus(404);
                    }

                    $resolvedArguments[] = $entity;
                }
            }
        }

        return $callable(...$resolvedArguments);
    }

    public function createReflectionForCallable(callable $callable): \ReflectionFunctionAbstract
    {
        return is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable[0]);
    }
}