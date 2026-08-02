<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'code',

        'title',

        'type',

        'value',

        'max_discount',

        'minimum_purchase',

        'usage_limit',

        'used_count',

        'starts_at',

        'expires_at',

        'is_active',

    ];



    protected function casts(): array
    {
        return [

            'value' => 'integer',

            'max_discount' => 'integer',

            'minimum_purchase' => 'integer',

            'starts_at' => 'datetime',

            'expires_at' => 'datetime',

            'is_active' => 'boolean',

        ];
    }



    // بررسی معتبر بودن کد
    public function isValid(): bool
    {
        return $this->is_active
            && (!$this->expires_at || now()->lessThan($this->expires_at));
    }

}
