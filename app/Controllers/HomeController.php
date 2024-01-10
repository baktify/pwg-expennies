<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ResponseFormatter;
use App\Services\CacheService;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;
use Slim\Views\Twig;

class HomeController
{
    public function __construct(
        private readonly Twig               $twig,
        private readonly TransactionService $transactionService,
        private readonly CategoryService    $categoryService,
        private readonly ResponseFormatter  $responseFormatter,
        private readonly CacheService       $cacheService,
    )
    {
    }

    public function index(Response $response): Response
    {
        $startDate = \DateTime::createFromFormat('Y-m-d', date('2023-12-01'));
        $endDate = new \DateTime('now');

        $totals = $this->cacheService->getOrSet(
            'totals', fn() => $this->transactionService->getTotals($startDate, $endDate)
        );
        $recentTransactions = $this->cacheService->getOrSet(
            'recentTransactions', fn() => $this->transactionService->getRecentTransactions(10)
        );
        $topSpendingCategories = $this->cacheService->getOrSet(
            'topSpendingCategories', fn() => $this->categoryService->getTopSpendingCategories(4)
        );

        return $this->twig->render($response, 'dashboard.twig', [
            'totals' => $totals,
            'transactions' => $recentTransactions,
            'topSpendingCategories' => $topSpendingCategories,
        ]);
    }

    public function getYearToDateStatistics(Response $response): Response
    {
        $year = (int)date('Y');

        $data = $this->cacheService->getOrSet(
            'monthlySummary', fn() => $this->transactionService->getMonthlySummary($year)
        );

        return $this->responseFormatter->asJson($response, $data);
    }
}
