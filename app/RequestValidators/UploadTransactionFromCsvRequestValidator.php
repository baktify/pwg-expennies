<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\ValidationException;
use Psr\Http\Message\UploadedFileInterface;

class UploadTransactionFromCsvRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        if (empty($data)) {
            throw new ValidationException(['csv' => ['Please, select a csv file']]);
        }

        /** @var UploadedFileInterface $csvFile */
        $csvFile = reset($data);

        if ($csvFile->getError() !== UPLOAD_ERR_OK) {
            throw new ValidationException(['csv' => ['Could not upload a file, try again']]);
        }

        if ($csvFile->getSize() > 7 * 1024 * 1024) {
            throw new ValidationException(['csv' => ['Maximum file size is 7Mb']]);
        }

        return $data;
    }
}