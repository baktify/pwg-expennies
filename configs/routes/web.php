<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    $app->get('/test', function (ServerRequestInterface $request, ResponseInterface $response) {
        $faker = Faker\Factory::create();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get(\Doctrine\ORM\EntityManager::class);
        $user = $em->find(\App\Entities\User::class, 8);

        for ($i = 0; $i < 100; $i++) {
            $category  = new \App\Entities\Category();
            $category->setName($faker->word());
            $category->setUser($user);

            $em->persist($category);
        }
        $em->flush();
        return $response;
    });

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);

    $app->group('/categories', function(RouteCollectorProxy $categories){
        $categories->get('', [CategoryController::class, 'index']);
        $categories->get('/load', [CategoryController::class, 'load']);
        $categories->post('', [CategoryController::class, 'store']);
        $categories->delete('/{id:[0-9]+}', [CategoryController::class, 'delete']);
        $categories->get('/{id:[0-9]+}', [CategoryController::class, 'getOne']);
        $categories->put('/{id:[0-9]+}', [CategoryController::class, 'update']);
    })->add(AuthMiddleware::class);
};
