<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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

            'user_id' => $this->user_id,

            'purchase_id' => $this->purchase_id,

            'plan_id' => $this->plan_id,

            'status' => $this->status,

            'starts_at' => optional(
                $this->starts_at
            )->toDateTimeString(),

            'expires_at' => optional(
                $this->expires_at
            )->toDateTimeString(),

            'cancelled_at' => optional(
                $this->cancelled_at
            )->toDateTimeString(),

            'remaining_days' => $this->remainingDays(),

            'is_active' => $this->isActive(),

            'is_expired' => $this->isExpired(),

            'is_cancelled' => $this->isCancelled(),

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}
