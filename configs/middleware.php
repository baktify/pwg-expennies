<?php

declare(strict_types = 1);

use App\Config;
use App\Middlewares\ValidationExceptionMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(function (Request $request, RequestHandler $handler) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

        return $handler->handle($request);
    });

    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    $app->add(ValidationExceptionMiddleware::class);
};
