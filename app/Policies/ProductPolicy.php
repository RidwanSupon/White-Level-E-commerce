<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('product.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('product.create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('product.update');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('product.delete');
    }
}
