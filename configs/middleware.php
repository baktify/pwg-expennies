<?php

declare(strict_types=1);

use App\Config;
use App\Enums\AppEnvironment;
use App\Middlewares\ActiveNavLinkMiddleware;
use App\Middlewares\CsrfFieldsMiddleware;
use App\Middlewares\InvalidLinkMiddleware;
use App\Middlewares\RedirectFromVerifyRouteIfUserVerifiedMiddleware;
use App\Middlewares\SessionStartMiddleware;
use App\Middlewares\TestMiddleware;
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
    $config = $container->get(Config::class);

    $app->add(ActiveNavLinkMiddleware::class);

    $app->add(InvalidLinkMiddleware::class);

    $app->add(MethodOverrideMiddleware::class);

    $app->add(CsrfFieldsMiddleware::class);

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(ValidationExceptionMiddleware::class);

    $app->add(TwigValidationErrorsMiddleware::class);

    $app->add(TwigValidationOldValuesMiddleware::class);

    $app->add('csrf');

    $app->add(TestMiddleware::class);
    $app->addBodyParsingMiddleware();

    $app->addRoutingMiddleware();


    $app->add(SessionStartMiddleware::class);

    if (AppEnvironment::isDevelopment($config->get('app_environment'))) {
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }

    $app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());

};
