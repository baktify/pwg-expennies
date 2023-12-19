<?php

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptsRequestValidator;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReceiptController
{
    public function __construct(
        private readonly Filesystem                       $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
    )
    {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UploadReceiptsRequestValidator::class)->validate(
            $request->getUploadedFiles()['receipts'] ?? []
        );

        foreach ($data as $uploadFile) {
            $filename = 'receipts/' . $uploadFile->getClientFilename();
            $fileContents = $uploadFile->getStream()->getContents();

            $this->filesystem->write($filename, $fileContents);
        }

        $response->getBody()->write('Receipts uploaded');
        return $response;
    }
}