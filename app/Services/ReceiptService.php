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
        private readonly EntityManager      $em,
        private readonly TransactionService $transactionService,
        private readonly Filesystem         $filesystem,
    )
    {
    }

    public function create($transaction, string $filename, string $storageFilename, string $mediaType): Receipt
    {
        $receipt = new Receipt();
        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setMediaType($mediaType);
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
            $mediaType = $uploadFile->getClientMediaType();

            $this->filesystem->write('receipts/' . $storageFilename, $fileContents);

            $this->create($transaction, $filename, $storageFilename, $mediaType);
        }
    }

    public function getById(int $receiptId): ?Receipt
    {
        return $this->em->getRepository(Receipt::class)->find($receiptId);
    }

    public function getTransactionReceipt(array $args): ?Receipt
    {
        $transactionId = (int)$args['transactionId'];
        $receiptId = (int)$args['receiptId'];

        if (!$transactionId || !($transaction = $this->transactionService->getById($transactionId))) {
            return null;
        }

        if (!$receiptId || !($receipt = $this->getById($receiptId))) {
            return null;
        }

        if (!($transaction->getReceipts()
            ->map(fn(Receipt $receipt) => $receipt->getId() === $receiptId)
            ->count()
        )) {
            return null;
        }

        return $receipt;
    }

    public function delete(Receipt $receipt): bool
    {
        $filepath = '/receipts/' . $receipt->getStorageFilename();
        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }

        $this->em->remove($receipt);
        $this->em->flush();

        return true;
    }
}