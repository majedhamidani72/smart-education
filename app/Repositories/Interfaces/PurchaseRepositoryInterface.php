<?php

namespace App\Repositories\Interfaces;

interface PurchaseRepositoryInterface extends BaseRepositoryInterface
{
    public function findByInvoiceNumber(
        string $invoiceNumber
    );

    public function getByUser(
        int $userId
    );

    public function getPending();

    public function getPaid();

    public function markAsPaid(
        int $purchaseId
    );

    public function markAsFailed(
        int $purchaseId
    );

    public function markAsCancelled(
        int $purchaseId
    );

    public function markAsRefunded(
        int $purchaseId
    );
}
