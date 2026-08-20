<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    protected $fillable = [

        'user_id',

        'photo',

        'years_of_experience',

        'card_number',

    ];

    protected function casts(): array
    {
        return [

            'years_of_experience' => 'integer',

        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * آدرس کامل عکس پروفایل (روی دیسک public).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo)
            : null;
    }
}
