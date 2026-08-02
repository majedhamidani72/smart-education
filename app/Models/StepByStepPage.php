<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepByStepPage extends Model
{
    use HasFactory;



    protected $fillable = [

        'content_item_id',

        'page_number',

        'image',

        'sort_order',

        'is_free',

    ];



    protected function casts(): array
    {
        return [

            'is_free' => 'boolean',

        ];
    }



    // هر صفحه متعلق به یک ContentItem است
    public function contentItem()
    {
        return $this->belongsTo(
            ContentItem::class
        );
    }

}
