<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;



    protected $fillable = [

        'user_id',

        'device_uuid',

        'device_name',

        'platform',

        'app_version',

        'last_seen_at',

    ];



    protected function casts(): array
    {
        return [

            'last_seen_at' => 'datetime',

        ];
    }



    // هر دستگاه متعلق به یک کاربر است
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

}
