<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptsRequestValidator;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly ReceiptService                   $receiptService,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService               $transactionService,
        private readonly Filesystem                       $filesystem,
    )
    {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        if (!$id || !($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $receipts = $this->requestValidatorFactory->make(UploadReceiptsRequestValidator::class)->validate(
            $request->getUploadedFiles()['receipts'] ?? []
        );

        $this->receiptService->uploadFiles($transaction, $receipts);

        $response->getBody()->write('Receipts uploaded');

        return $response;
    }

    public function download(Request $request, Response $response, array $args): Response
    {
        $receipt = $this->receiptService->getTransactionReceipt($args);

        if (!$receipt) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $file = $this->filesystem->readStream('/receipts/' . $receipt->getStorageFilename());

        return $response
            ->withHeader('Content-Disposition', 'inline; filename="' . $receipt->getFilename() . '"')
            ->withHeader('Content-Type', $receipt->getMediaType())
            ->withBody(new Stream($file));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $receipt = $this->receiptService->getTransactionReceipt($args);

        if (!$receipt) {
            $response->getBody()->write('Transaction or receipt not found');
            return $response->withStatus(404);
        }

        $this->receiptService->delete($receipt);

        $response->getBody()->write('Receipt deleted.');

        return $response;
    }
}