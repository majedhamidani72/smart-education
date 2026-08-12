<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    public function before(
        User $user,
        string $ability
    ): ?bool {

        if ($user->hasRole('SuperAdmin')) {

            return true;

        }

        return null;
    }


    public function viewAny(
        User $user
    ): bool {

        return $user->hasAnyRole([
            'Admin',
            'Teacher',
        ]);

    }


    public function view(
        User $user,
        Video $video
    ): bool {

        if ($user->hasRole('Admin')) {

            return true;

        }


        if ($user->hasRole('Teacher')) {

            return $video->uploaded_by === $user->id;

        }


        return false;
    }


    public function create(
        User $user
    ): bool {

        return $user->hasAnyRole([
            'Admin',
            'Teacher',
        ]);

    }


    public function update(
        User $user,
        Video $video
    ): bool {

        if ($user->hasRole('Admin')) {

            return true;

        }


        if ($user->hasRole('Teacher')) {

            return $video->uploaded_by === $user->id;

        }


        return false;
    }


    public function delete(
        User $user,
        Video $video
    ): bool {

        if ($user->hasRole('Admin')) {

            return true;

        }


        if ($user->hasRole('Teacher')) {

            return $video->uploaded_by === $user->id;

        }


        return false;
    }


    public function approve(
        User $user,
        Video $video
    ): bool {

        return $user->hasRole('Admin')
            && $video->processing_status === 'pending';

    }


    public function reject(
        User $user,
        Video $video
    ): bool {

        return $user->hasRole('Admin')
            && $video->processing_status === 'pending';

    }


    public function restore(
        User $user,
        Video $video
    ): bool {

        return $user->hasRole('Admin');

    }


    public function forceDelete(
        User $user,
        Video $video
    ): bool {

        return $user->hasRole('Admin');

    }
}
