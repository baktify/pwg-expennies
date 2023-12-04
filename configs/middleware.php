<?php

declare(strict_types = 1);

use App\Config;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\StartSessionMiddleware;
use App\Middlewares\TwigValidationErrorsMiddleware;
use App\Middlewares\TwigValidationOldValuesMiddleware;
use App\Middlewares\ValidationExceptionMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(ValidationExceptionMiddleware::class);

    $app->add(TwigValidationErrorsMiddleware::class);

    $app->add(TwigValidationOldValuesMiddleware::class);

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(StartSessionMiddleware::class);

    $app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
};
