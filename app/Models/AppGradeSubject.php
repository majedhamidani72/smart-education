<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppGradeSubject extends Model
{
    use HasFactory;

    protected $table = 'app_grade_subjects';


    // فیلدهای قابل ذخیره
    protected $fillable = [

        'app_id',
        // شناسه اپلیکیشن

        'grade_id',
        // شناسه پایه

        'subject_id',
        // شناسه درس

    ];



    // =========================
    // Relationships
    // =========================


    // هر رکورد متعلق به یک App است
    public function app()
    {
        return $this->belongsTo(App::class);
    }



    // هر رکورد متعلق به یک Grade است
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }



    // هر رکورد متعلق به یک Subject است
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

}
