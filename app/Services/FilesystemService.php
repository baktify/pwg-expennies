<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entities\Transaction;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Http\Message\UploadedFileInterface;

class FilesystemService
{
    public function __construct(
        private readonly Filesystem                    $filesystem,
        private readonly ReceiptService                $receiptService,
        private readonly EntityManagerServiceInterface $entityManager,
    )
    {
    }

    public function uploadTransactionReceiptFiles(Transaction $transaction, array $receiptFiles): void
    {
        /** @var UploadedFileInterface $uploadFile */
        foreach ($receiptFiles as $uploadFile) {
            $filename = $uploadFile->getClientFilename();
            $storageFilename = bin2hex(random_bytes(25));
            $fileContents = $uploadFile->getStream()->getContents();
            $mediaType = $uploadFile->getClientMediaType();

            $this->filesystem->write('receipts/' . $storageFilename, $fileContents);

            $receipt = $this->receiptService->create($transaction, $filename, $storageFilename, $mediaType);
            $this->entityManager->sync($receipt);
        }
    }

    /**
     * @return resource
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    public function remove(string $filepath): void
    {
        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }
}