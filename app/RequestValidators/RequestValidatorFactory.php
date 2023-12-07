<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\RequestValidatorInterface;
use Psr\Container\ContainerInterface;

class RequestValidatorFactory implements RequestValidatorFactoryInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function make(string $class): RequestValidatorInterface
    {
        $requestValidator = $this->container->get($class);

        if($requestValidator instanceof RequestValidatorInterface) {
            return $requestValidator;
        }

        // TODO: Create custom exception instead of the one below
        throw new \RuntimeException('Failed to create a ' . $class . ' request validator class');
    }
}