<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    /**
     * تبدیل اطلاعات به آرایه
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'invoice_number' => $this->invoice_number,

            'user_id' => $this->user_id,

            'total_amount' => (int) $this->total_amount,

            'discount_amount' => (int) $this->discount_amount,

            'payable_amount' => (int) $this->payable_amount,

            'status' => $this->status,

            'paid_at' => optional(
                $this->paid_at
            )->toDateTimeString(),

            'notes' => $this->notes,

            'items' => PurchaseItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}
