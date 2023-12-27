<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\ReceiptController;
use App\Controllers\SeedController;
use App\Controllers\TestController;
use App\Controllers\TransactionController;
use App\Controllers\VerificationController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\EntityBindingRouteStrategy;
use App\Middlewares\RedirectVerifiedUserMiddleware;
use App\Middlewares\ValidateSignatureMiddleware;
use App\Middlewares\VerifyEmailMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/test', [TestController::class, 'test']);
    $app->get('/seed', [SeedController::class, 'index']);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/verify', [VerificationController::class, 'index']);
        $group->get('/verify/{userId}/{emailHash}', [VerificationController::class, 'verify'])
            ->setName('verify')->add(ValidateSignatureMiddleware::class);
        $group->get('/resend-verification', [VerificationController::class, 'resendVerification']);
    })
        ->add(RedirectVerifiedUserMiddleware::class)
        ->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $group) {
        $group->post('/logout', [AuthController::class, 'logOut']);
    })->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/', [HomeController::class, 'index']);

        $group->group('/categories', function (RouteCollectorProxy $categories) {
            $categories->get('', [CategoryController::class, 'index']);
            $categories->get('/list', [CategoryController::class, 'list']);
            $categories->get('/load', [CategoryController::class, 'load']);
            $categories->post('', [CategoryController::class, 'store']);
            $categories->get('/{category}', [CategoryController::class, 'get']);
            $categories->delete('/{category}', [CategoryController::class, 'delete']);
            $categories->put('/{category}', [CategoryController::class, 'update']);
        });

        $group->group('/transactions', function (RouteCollectorProxy $transactions) {
            $transactions->get('', [TransactionController::class, 'index']);
            $transactions->get('/load', [TransactionController::class, 'load']);
            $transactions->post('', [TransactionController::class, 'store']);
            $transactions->post('/upload-from-csv', [TransactionController::class, 'uploadFromCsv']);
            $transactions->get('/{transaction}', [TransactionController::class, 'get']);
            $transactions->delete('/{transaction}', [TransactionController::class, 'delete']);
            $transactions->put('/{transaction}', [TransactionController::class, 'update']);
            $transactions->put('/{transaction}/toggle-review', [TransactionController::class, 'toggleReview']);
            $transactions->put('/{transaction}/receipts', [ReceiptController::class, 'store']);
            $transactions->get('/{transaction}/receipts/{receipt}', [ReceiptController::class, 'download']);
            $transactions->delete('/{transaction}/receipts/{receipt}', [ReceiptController::class, 'delete']);
        });
    })
        ->add(VerifyEmailMiddleware::class)
        ->add(AuthMiddleware::class);
};
