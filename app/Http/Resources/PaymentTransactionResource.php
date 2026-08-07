<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
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

            'user_id' => $this->user_id,

            'gateway' => $this->gateway,

            'authority' => $this->authority,

            'transaction_id' => $this->transaction_id,

            'reference_id' => $this->reference_id,

            'amount' => (int) $this->amount,

            'currency' => $this->currency,

            'status' => $this->status,

            'card_pan' => $this->card_pan,

            'message' => $this->message,

            'gateway_response' => $this->gateway_response,

            'paid_at' => optional(
                $this->paid_at
            )->toDateTimeString(),

            'verified_at' => optional(
                $this->verified_at
            )->toDateTimeString(),

            'is_paid' => $this->isPaid(),

            'is_pending' => $this->isPending(),

            'is_failed' => $this->isFailed(),

            'is_refunded' => $this->isRefunded(),

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}
