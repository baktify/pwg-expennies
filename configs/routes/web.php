<?php

declare(strict_types = 1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\ReceiptController;
use App\Controllers\SeedController;
use App\Controllers\TestController;
use App\Controllers\TransactionController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);
    $app->get('/test', [TestController::class, 'test']);

    $app->get('/seed', [SeedController::class, 'index']);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);

    $app->group('/categories', function(RouteCollectorProxy $categories){
        $categories->get('', [CategoryController::class, 'index']);
        $categories->get('/list', [CategoryController::class, 'list']);
        $categories->get('/load', [CategoryController::class, 'load']);
        $categories->post('', [CategoryController::class, 'store']);
        $categories->delete('/{id:[0-9]+}', [CategoryController::class, 'delete']);
        $categories->get('/{id:[0-9]+}', [CategoryController::class, 'getOne']);
        $categories->put('/{id:[0-9]+}', [CategoryController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/transactions', function (RouteCollectorProxy $transactions) {
        $transactions->get('', [TransactionController::class, 'index']);
        $transactions->get('/load', [TransactionController::class, 'load']);
        $transactions->post('', [TransactionController::class, 'store']);
        $transactions->get('/{id:[0-9]+}', [TransactionController::class, 'getOne']);
        $transactions->delete('/{id:[0-9]+}', [TransactionController::class, 'delete']);
        $transactions->put('/{id:[0-9]+}', [TransactionController::class, 'update']);
        $transactions->put('/{id:[0-9]+}/receipts', [ReceiptController::class, 'store']);
        $transactions->get(
            '/{transactionId:[0-9]+}/receipts/{receiptId:[0-9]+}',
            [ReceiptController::class, 'download']
        );
        $transactions->delete(
            '/{transactionId:[0-9]+}/receipts/{receiptId:[0-9]+}',
            [ReceiptController::class, 'delete']
        );
    })->add(AuthMiddleware::class);
};
