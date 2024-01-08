<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Clockwork\Clockwork;
use Clockwork\Request\LogLevel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class HomeController
{
    public function __construct(
        private readonly Twig               $twig,
        private readonly TransactionService $transactionService,
        private readonly CategoryService    $categoryService,
        private readonly ResponseFormatter  $responseFormatter,
        private readonly Clockwork          $clockwork,
    )
    {
    }

    public function index(Response $response): Response
    {
        $startDate = \DateTime::createFromFormat('Y-m-d', date('2023-01-01'));
        $endDate = \DateTime::createFromFormat('Y-m-d', date('2023-12-31'));
        $totals = $this->transactionService->getTotals($startDate, $endDate);
        $recentTransactions = $this->transactionService->getRecentTransactions(10);

        $topSpendingCategories = $this->categoryService->getTopSpendingCategories(4);

        return $this->twig->render($response, 'dashboard.twig', [
            'totals' => $totals,
            'transactions' => $recentTransactions,
            'topSpendingCategories' => $topSpendingCategories,
        ]);
    }

    public function getYearToDateStatistics(Request $request, Response $response): Response
    {
        $year = 2023;
//        $year = (int)$request->getParsedBody()['year'] ?: (int)date('Y');
        $data = $this->transactionService->getMonthlySummary($year);

        return $this->responseFormatter->asJson($response, $data);
    }
}
