<?php

declare(strict_types=1);

use App\Middlewares\CsrfFieldsMiddleware;
use App\Middlewares\SessionStartMiddleware;
use App\Middlewares\TwigValidationErrorsMiddleware;
use App\Middlewares\TwigValidationOldValuesMiddleware;
use App\Middlewares\ValidationExceptionMiddleware;
use Clockwork\Support\Slim\ClockworkMiddleware;
use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Clockwork\Clockwork;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(MethodOverrideMiddleware::class);

    $app->add(CsrfFieldsMiddleware::class);

    $app->add('csrf');

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(ValidationExceptionMiddleware::class);

    $app->add(TwigValidationErrorsMiddleware::class);

    $app->add(TwigValidationOldValuesMiddleware::class);

    $app->add(SessionStartMiddleware::class);

    $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));

    $app->addBodyParsingMiddleware();

    $app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
};
