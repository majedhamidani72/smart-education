
<?php

namespace App\Repositories\Interfaces;

interface PurchaseItemRepositoryInterface extends BaseRepositoryInterface
{
    public function getByPurchase(
        int $purchaseId
    );

    public function getByItem(
        string $itemType,
        int $itemId
    );

    public function createItem(
        array $data
    );
}
