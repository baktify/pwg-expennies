<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\ValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Psr\Http\Message\UploadedFileInterface;

class UploadReceiptsRequestValidator implements RequestValidatorInterface
{

    public function validate(array $data): array
    {
        if (!count($data)) {
            throw new ValidationException(['receipts' => ['Please, select receipt files']]);
        }

        /** @var UploadedFileInterface $uploadFile */
        foreach ($data as $uploadFile) {
            // 1. Check if the file exists and if UPLOAD_ERR_OK
            if ($uploadFile && $uploadFile->getError() !== UPLOAD_ERR_OK) {
                throw new ValidationException(['receipts' => ['Failed to upload a receipt file']]);
            }

            // 2. Check file size > 5 megabytes
            if ($uploadFile->getSize() > 5 * 1024 * 1024) {
                throw new ValidationException(['receipts' => ['Maximum file size is 5MB']]);
            }

            // 3. Check clientFilename() is according to regex
            if (!preg_match('/^[a-zA-Z0-9\s.-_]+$/', $uploadFile->getClientFilename())) {
                throw new ValidationException(['receipts' => ['Invalid filename']]);
            }

            // 4. Check if file has correct extension and mimetype
            $allowedMimetypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
            $allowedExtensions = ['png', 'jpeg', 'jpg', 'pdf'];

            if (!in_array($uploadFile->getClientMediaType(), $allowedMimetypes)) {
                throw new ValidationException(['receipts' => ['Allowed extensions are pdf, png, jpg, jpeg.']]);
            }

            $detector = new FinfoMimeTypeDetector();
            $filePath = $uploadFile->getStream()->getMetadata('uri');
            $fileContents = $uploadFile->getStream()->getContents();

            $mimeType = $detector->detectMimeType($filePath, $fileContents);

            if ( !in_array($mimeType, $allowedMimetypes)) {
                throw new ValidationException(['receipts' => ['Invalid file']]);
            }
        }

        return $data;
    }
}