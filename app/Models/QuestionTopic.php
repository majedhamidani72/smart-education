<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class QuestionTopic extends Model
{
    use HasFactory, SoftDeletes;


    // فیلدهای قابل ذخیره
    protected $fillable = [

        'title', // نام موضوع

        'description', // توضیحات موضوع

    ];



    // =========================
    // Relationships
    // =========================


    // یک موضوع چند سوال دارد
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
