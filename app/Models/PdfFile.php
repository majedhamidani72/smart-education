<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfFile extends Model
{
    use HasFactory;



    protected $fillable = [

        'content_item_id',

        'title',

        'file',

        'file_size',

    ];



    // هر PDF متعلق به یک ContentItem است
    public function contentItem()
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }
}
