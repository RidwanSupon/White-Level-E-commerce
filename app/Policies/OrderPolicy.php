<?php

namespace App\Policies;

use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('order.view');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('order.update');
    }

    public function cancel(User $user): bool
    {
        return $user->hasPermission('order.cancel');
    }

    public function refund(User $user): bool
    {
        return $user->hasPermission('order.refund');
    }
}
