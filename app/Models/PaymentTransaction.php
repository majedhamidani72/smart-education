<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentTransaction extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('payment_transactions');
    }

    protected $fillable = [

        'purchase_id',

        'user_id',

        'gateway',

        'authority',

        'transaction_id',

        'reference_id',

        'amount',

        'currency',

        'status',

        'card_pan',

        'message',

        'gateway_response',

        'return_to',

        'paid_at',

        'verified_at',

    ];

    protected function casts(): array
    {
        return [

            'gateway_response' => 'array',

            'paid_at' => 'datetime',

            'verified_at' => 'datetime',

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
