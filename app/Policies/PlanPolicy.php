<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Plan;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('plans.view');
    }

    public function view(
        User $user,
        Plan $plan
    ): bool
    {
        return $user->can('plans.view');
    }

    public function create(User $user): bool
    {
        return $user->can('plans.create');
    }

    public function update(
        User $user,
        Plan $plan
    ): bool
    {
        return $user->can('plans.update');
    }

    public function delete(
        User $user,
        Plan $plan
    ): bool
    {
        return $user->can('plans.delete');
    }

    public function restore(
        User $user,
        Plan $plan
    ): bool
    {
        return $user->can('plans.update');
    }

    public function forceDelete(
        User $user,
        Plan $plan
    ): bool
    {
        return $user->can('plans.delete');
    }
}
