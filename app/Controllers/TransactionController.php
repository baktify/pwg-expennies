<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Transaction;
use App\RequestValidators\TransactionCreateRequestValidator;
use App\RequestValidators\TransactionUpdateRequestValidator;
use App\RequestValidators\UploadTransactionFromCsvRequestValidator;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\CsvFileService;
use App\Services\RequestService;
use App\Services\TransactionImportService;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;
use Slim\Views\Twig;

class TransactionController
{
    public function __construct(
        private readonly Twig                             $twig,
        private readonly RequestService                   $requestService,
        private readonly TransactionService               $transactionService,
        private readonly TransactionImportService         $transactionImportService,
        private readonly ResponseFormatter                $responseFormatter,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService                  $categoryService,
        private readonly CsvFileService                   $csvParserService,
        private readonly EntityManagerServiceInterface    $entityManager,
        private readonly CacheInterface                   $cache,
    )
    {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'transactions/index.twig');
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParams($request);

        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $totalTransactions = count($transactions);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($this->transactionService->getDataTableMapper(), (array)$transactions->getIterator()),
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
        $this->entityManager->sync($transaction);

        return $this->responseFormatter->asJson(
            $response,
            $this->transactionService->toArray($transaction)
        );
    }

    public function get(Response $response, Transaction $transaction): Response
    {
        return $this->responseFormatter->asJson(
            $response,
            $this->transactionService->toArray($transaction, false)
        );
    }

    public function update(Request $request, Response $response, Transaction $transaction): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionUpdateRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $category = $data['categoryId']
            ? $this->categoryService->getById((int)$data['categoryId'])
            : null;

        $this->transactionService->update($transaction, $data, $category);
        $this->entityManager->sync($transaction);

        return $this->responseFormatter->asJson(
            $response,
            ['message' => 'Update success']
        );
    }

    public function toggleReview(Response $response, Transaction $transaction): Response
    {
        $this->transactionService->toggleReview($transaction);
        $this->entityManager->sync();

        $response->getBody()->write('Review was toggled');
        return $response;
    }

    public function delete(Response $response, Transaction $transaction): Response
    {
        $this->entityManager->delete($transaction, true);

        return $this->responseFormatter->asJson(
            $response,
            ['message' => 'Transaction deleted']
        );
    }

    public function uploadFromCsv(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UploadTransactionFromCsvRequestValidator::class)->validate(
            $request->getUploadedFiles()
        );

        $csvFile = reset($data);
        $csvPath = $csvFile->getStream()->getMetadata('uri');

        $parsedTransactionRecords = $this->csvParserService->parseTransactionFile($csvPath);

        $this->transactionImportService->import($parsedTransactionRecords);

        return $this->responseFormatter->asJson($response, [
            'message' => 'Success',
        ]);
    }
}