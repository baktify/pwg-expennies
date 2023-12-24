<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\RequestValidators\UploadReceiptsRequestValidator;
use App\Services\FilesystemService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly FilesystemService                $filesystemService,
        private readonly EntityManagerServiceInterface    $entityManager
    )
    {
    }

    public function store(Request $request, Response $response, Transaction $transaction): Response
    {
        $receiptFiles = $this->requestValidatorFactory->make(UploadReceiptsRequestValidator::class)->validate(
            $request->getUploadedFiles()['receipts'] ?? null
        );

        $this->filesystemService->uploadTransactionReceiptFiles($transaction, $receiptFiles);

        $response->getBody()->write('Receipts uploaded');
        return $response;
    }

    public function download(Response $response, Transaction $transaction, Receipt $receipt): Response
    {
        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $file = $this->filesystemService->readStream('/receipts/' . $receipt->getStorageFilename());

        return $response
            ->withHeader('Content-Disposition', 'inline; filename="' . $receipt->getFilename() . '"')
            ->withHeader('Content-Type', $receipt->getMediaType())
            ->withBody(new Stream($file));
    }

    public function delete(Response $response, Transaction $transaction, Receipt $receipt): Response
    {
        if ($receipt->getTransaction()->getId() !== $transaction->getId()) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $this->filesystemService->remove('/receipts/' . $receipt->getStorageFilename());
        $this->entityManager->delete($receipt, true);

        $response->getBody()->write('Receipt deleted.');

        return $response;
    }
}