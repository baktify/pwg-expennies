<?php

declare(strict_types=1);

use Slim\App;

$container = require __DIR__ . '/../bootstrap.php';

$app = $container->get(App::class);

$app->run();
