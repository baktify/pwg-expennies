<?php

declare(strict_types = 1);

use App\Config;
use App\Middlewares\SessionStartMiddleware;
use App\Middlewares\TwigValidationRegisterErrorsMiddleware;
use App\Middlewares\TwigValidationRegisterOldValuesMiddleware;
use App\Middlewares\ValidationExceptionHandlerMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(ValidationExceptionHandlerMiddleware::class);

    $app->add(TwigValidationRegisterErrorsMiddleware::class);

    $app->add(TwigValidationRegisterOldValuesMiddleware::class);

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(SessionStartMiddleware::class);

    $app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
};
