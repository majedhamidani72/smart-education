<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    protected $table = 'content_progress';

    protected $fillable = [
        'user_id', 'content_item_id', 'watch_seconds', 'last_position_seconds',
        'completed', 'last_viewed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'last_viewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function contentItem() { return $this->belongsTo(ContentItem::class); }
}
