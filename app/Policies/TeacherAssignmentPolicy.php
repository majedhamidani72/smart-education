<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TeacherAssignment;

class TeacherAssignmentPolicy
{

    /**
     * مشاهده لیست
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->hasAnyRole([
            'SuperAdmin',
            'Admin',
            'Teacher',
        ]);
    }



    /**
     * مشاهده یک Assignment
     */
    public function view(
        User $user,
        TeacherAssignment $teacherAssignment
    ): bool
    {

        if (
            $user->hasAnyRole([
                'SuperAdmin',
                'Admin',
            ])
        ) {
            return true;
        }


        return $user->id === $teacherAssignment->teacher_id;

    }



    /**
     * ایجاد Assignment
     */
    public function create(
        User $user
    ): bool
    {
        return $user->hasAnyRole([
            'SuperAdmin',
            'Admin',
        ]);
    }



    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        TeacherAssignment $teacherAssignment
    ): bool
    {
        return $user->hasAnyRole([
            'SuperAdmin',
            'Admin',
        ]);
    }



    /**
     * حذف
     */
    public function delete(
        User $user,
        TeacherAssignment $teacherAssignment
    ): bool
    {
        return $user->hasAnyRole([
            'SuperAdmin',
            'Admin',
        ]);
    }



    /**
     * بازگردانی حذف شده
     */
    public function restore(
        User $user,
        TeacherAssignment $teacherAssignment
    ): bool
    {
        return $user->hasRole(
            'SuperAdmin'
        );
    }



    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        TeacherAssignment $teacherAssignment
    ): bool
    {
        return $user->hasRole(
            'SuperAdmin'
        );
    }

}
