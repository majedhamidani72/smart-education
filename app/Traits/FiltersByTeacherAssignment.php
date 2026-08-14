<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByTeacherAssignment
{
    /**
     * اعمال فیلتر دسترسی معلم
     */
    protected static function applyTeacherFilter(
        Builder $query,
        string $relation = 'teacherAssignments'
    ): Builder {

        $user = auth()->user();


        if (! $user) {
            return $query;
        }



        /*
        |--------------------------------------------------------------------------
        | Admin ها دسترسی کامل دارند
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole('SuperAdmin')
            ||
            $user->hasRole('Admin')
        ) {

            return $query;

        }



        /*
        |--------------------------------------------------------------------------
        | Teacher فقط موارد اختصاص داده شده
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('Teacher')) {


            $query->whereHas(
                $relation,
                function (Builder $builder) use ($user) {


                    $builder->where(
                        'teacher_id',
                        $user->id
                    )
                    ->where(
                        'is_active',
                        true
                    );


                }
            );


        }


        return $query;
    }
}
