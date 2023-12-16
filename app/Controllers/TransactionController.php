<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Transaction;
use App\RequestValidators\TransactionCreateRequestValidator;
use App\RequestValidators\TransactionGetRequestValidator;
use App\RequestValidators\TransactionUpdateRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\RequestService;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly RequestService                   $requestService,
        private readonly TransactionService               $transactionService,
        private readonly ResponseFormatter                $responseFormatter,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService                  $categoryService,
    )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'transactions/index.twig');
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParams($request);

        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $totalTransactions = count($transactions);

        $mapper = $this->transactionService->getDataTableMapper();

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($mapper, (array)$transactions->getIterator()),
            $params->draw,
            $totalTransactions
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionCreateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $this->categoryService->getById((int)$data['categoryId']);

        $transaction = $this->transactionService->create(
            $data['description'],
            (float)$data['amount'],
            new \DateTime($data['date']),
            $request->getAttribute('user'),
            $category,
        );

        return $this->responseFormatter->asJson(
            $response,
            $this->transactionService->toArray($transaction)
        );
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $transaction = $this->transactionService->getOne((int)$args['id']);

        if (!$transaction) {
            return $this->responseFormatter->asJson(
                $response->withStatus(404),
                ['message' => 'Transaction not found']
            );
        }

        return $this->responseFormatter->asJson(
            $response,
            $this->transactionService->toArray($transaction, false)
        );
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionUpdateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $transaction = $this->transactionService->update((int) $args['id'], $data);

        return $this->responseFormatter->asJson(
            $response,
            ['message' => 'Update success']
        );
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionGetRequestValidator::class)->validate(
            $args
        );

        $result = $this->transactionService->delete((int)$data['id']);

        if (!$result) {
            return $this->responseFormatter->asJson(
                $response->withStatus(404),
                ['message' => 'Transaction not found']
            );
        }

        return $this->responseFormatter->asJson(
            $response,
            ['message' => 'Transaction deleted']
        );
    }
}