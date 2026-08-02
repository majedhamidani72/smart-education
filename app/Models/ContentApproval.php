<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentApproval extends Model
{
    use HasFactory;


    protected $fillable = [

        'content_item_id',

        'admin_id',

        'status',

        'note',

    ];



    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class);
    }



    public function admin()
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }

}
