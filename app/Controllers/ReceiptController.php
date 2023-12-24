<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptsRequestValidator;
use App\Services\FilesystemService;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly ReceiptService                   $receiptService,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService               $transactionService,
        private readonly FilesystemService                $filesystemService,
        private readonly EntityManagerServiceInterface    $entityManager
    )
    {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $transaction = $this->transactionService->getById((int)$args['id']);

        if (!($transaction)) {
            return $response->withStatus(404);
        }

        $receiptFiles = $this->requestValidatorFactory->make(UploadReceiptsRequestValidator::class)->validate(
            $request->getUploadedFiles()['receipts'] ?? null
        );

        $this->filesystemService->uploadTransactionReceiptFiles($transaction, $receiptFiles);

        $response->getBody()->write('Receipts uploaded');
        return $response;
    }

    public function download(Request $request, Response $response, array $args): Response
    {
        $transactionId = (int)$args['transactionId'];
        $receiptId = (int)$args['receiptId'];

        if (!$transactionId || !$this->transactionService->getById($transactionId)) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        if (!$receiptId || !($receipt = $this->receiptService->getById($receiptId))) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        if ($receipt->getTransaction()->getId() !== $transactionId) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $file = $this->filesystemService->readStream('/receipts/' . $receipt->getStorageFilename());

        return $response
            ->withHeader('Content-Disposition', 'inline; filename="' . $receipt->getFilename() . '"')
            ->withHeader('Content-Type', $receipt->getMediaType())
            ->withBody(new Stream($file));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $transactionId = (int)$args['transactionId'];
        $receiptId = (int)$args['receiptId'];

        if (!$this->transactionService->getById($transactionId)) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        if (!($receipt = $this->receiptService->getById($receiptId))) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        if ($receipt->getTransaction()->getId() !== $transactionId) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $this->filesystemService->remove('/receipts/' . $receipt->getStorageFilename());
        $this->entityManager->delete($receipt, true);

        $response->getBody()->write('Receipt deleted.');

        return $response;
    }
}