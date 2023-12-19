<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptsRequestValidator;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReceiptController
{
    public function __construct(
        private readonly ReceiptService                   $receiptService,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService               $transactionService,
    )
    {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        if (!$id || !($transaction = $this->transactionService->getOne($id))) {
            return $response->withStatus(404);
        }

        $receipts = $this->requestValidatorFactory->make(UploadReceiptsRequestValidator::class)->validate(
            $request->getUploadedFiles()['receipts'] ?? []
        );

        $this->receiptService->uploadFiles($transaction, $receipts);

        $response->getBody()->write('Receipts uploaded');
        return $response;
    }
}