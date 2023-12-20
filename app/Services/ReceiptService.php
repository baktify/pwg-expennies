<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Receipt;
use App\Entities\Transaction;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Psr\Http\Message\UploadedFileInterface;

class ReceiptService
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly Filesystem    $filesystem,
    )
    {
    }

    public function create($transaction, string $filename, string $storageFilename): Receipt
    {
        $receipt = new Receipt();
        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setCreatedAt(new \DateTime());

        $this->em->persist($receipt);
        $this->em->flush();

        return $receipt;
    }

    public function uploadFiles(Transaction $transaction, array $receipts)
    {
        /** @var UploadedFileInterface $uploadFile */
        foreach ($receipts as $uploadFile) {
            $filename = $uploadFile->getClientFilename();
            $fileContents = $uploadFile->getStream()->getContents();
            $storageFilename = bin2hex(random_bytes(25));

            $this->filesystem->write('receipts/' . $storageFilename, $fileContents);

            $this->create($transaction, $filename, $storageFilename);
        }
    }

    public function getById(int $receiptId): ?Receipt
    {
        return $this->em->getRepository(Receipt::class)->find($receiptId);
    }
}