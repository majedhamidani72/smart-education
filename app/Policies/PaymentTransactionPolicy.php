<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentTransaction;

class PaymentTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payment-transactions.view');
    }

    public function view(
        User $user,
        PaymentTransaction $paymentTransaction
    ): bool
    {
        return $user->can('payment-transactions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payment-transactions.create');
    }

    public function update(
        User $user,
        PaymentTransaction $paymentTransaction
    ): bool
    {
        return $user->can('payment-transactions.update');
    }

    public function delete(
        User $user,
        PaymentTransaction $paymentTransaction
    ): bool
    {
        return $user->can('payment-transactions.delete');
    }

    public function restore(
        User $user,
        PaymentTransaction $paymentTransaction
    ): bool
    {
        return $user->can('payment-transactions.update');
    }

    public function forceDelete(
        User $user,
        PaymentTransaction $paymentTransaction
    ): bool
    {
        return $user->can('payment-transactions.delete');
    }
}
