<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Receipt;
use App\Entities\Transaction;

class ReceiptService extends EntityManagerService
{
    public function create(Transaction $transaction, string $filename, string $storageFilename, string $mediaType): Receipt
    {
        $receipt = new Receipt();
        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setMediaType($mediaType);
        $receipt->setCreatedAt(new \DateTime());

        $this->em->persist($receipt);

        return $receipt;
    }

    public function getById(int $receiptId): ?Receipt
    {
        return $this->em->getRepository(Receipt::class)->find($receiptId);
    }

    public function delete(Receipt $receipt): void
    {
        $this->em->remove($receipt);
    }
}