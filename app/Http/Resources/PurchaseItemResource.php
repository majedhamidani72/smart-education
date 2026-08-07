<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    /**
     * تبدیل Resource به آرایه
     */
    public function toArray(
        Request $request
    ): array
    {
        return [

            'id' => $this->id,

            'purchase_id' => $this->purchase_id,

            'item_type' => $this->item_type,

            'item_id' => $this->item_id,

            'title' => $this->title,

            'price' => (int) $this->price,

            'discount_amount' => (int) $this->discount_amount,

            'final_price' => (int) $this->final_price,

            'quantity' => (int) $this->quantity,

            'total_price' => $this->getTotalPrice(),

            'notes' => $this->notes,

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}
