<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleQuestion extends Model
{
    use HasFactory;



    protected $fillable = [

        'content_item_id',

        'title',

        'file',

    ];



    // هر نمونه سوال متعلق به یک ContentItem است
    public function contentItem()
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

}
