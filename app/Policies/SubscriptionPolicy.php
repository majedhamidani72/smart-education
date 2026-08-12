<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscriptions.view');
    }

    public function view(
        User $user,
        Subscription $subscription
    ): bool
    {
        return $user->can('subscriptions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('subscriptions.create');
    }

    public function update(
        User $user,
        Subscription $subscription
    ): bool
    {
        return $user->can('subscriptions.update');
    }

    public function delete(
        User $user,
        Subscription $subscription
    ): bool
    {
        return $user->can('subscriptions.delete');
    }

    public function restore(
        User $user,
        Subscription $subscription
    ): bool
    {
        return $user->can('subscriptions.update');
    }

    public function forceDelete(
        User $user,
        Subscription $subscription
    ): bool
    {
        return $user->can('subscriptions.delete');
    }
}
