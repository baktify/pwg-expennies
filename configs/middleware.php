<?php

declare(strict_types=1);

use App\Middlewares\CsrfFieldsMiddleware;
use App\Middlewares\MethodOverrideMiddleware;
use App\Middlewares\SessionStartMiddleware;
use App\Middlewares\TwigValidationErrorsMiddleware;
use App\Middlewares\TwigValidationOldValuesMiddleware;
use App\Middlewares\ValidationExceptionMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $container = $app->getContainer();

    $app->add($container->get(MethodOverrideMiddleware::class));

    $app->add(CsrfFieldsMiddleware::class);

    $app->add('csrf');

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(ValidationExceptionMiddleware::class);

    $app->add(TwigValidationErrorsMiddleware::class);

    $app->add(TwigValidationOldValuesMiddleware::class);

    $app->add(SessionStartMiddleware::class);

    $app->addBodyParsingMiddleware();

    $app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());
};
