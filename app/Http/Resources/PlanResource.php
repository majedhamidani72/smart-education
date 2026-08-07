<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * تبدیل اطلاعات به آرایه
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'planable_type' => $this->planable_type,

            'planable_id' => $this->planable_id,

            'price' => (int) $this->price,

            'discount_price' => $this->discount_price
                ? (int) $this->discount_price
                : null,

            'final_price' => $this->finalPrice(),

            'discount_amount' => $this->discountAmount(),

            'discount_percent' => $this->discountPercent(),

            'purchase_type' => $this->purchase_type,

            'duration_days' => $this->duration_days,

            'is_active' => $this->isActive(),

            'sort_order' => $this->sort_order,

            'starts_at' => optional(
                $this->starts_at
            )->toDateTimeString(),

            'expires_at' => optional(
                $this->expires_at
            )->toDateTimeString(),

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}
