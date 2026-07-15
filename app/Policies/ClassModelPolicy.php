<?php

namespace App\Policies;

use App\Models\ClassModel;
use App\Models\User;

class ClassModelPolicy
{
    public function view(User $user, ClassModel $class)
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($class->admin_id === $user->id) {
            return true;
        }

        return $class->members()->where('user_id', $user->id)->exists();
    }

    public function manage(User $user, ClassModel $class)
    {
        return $user->role === 'admin' || $class->admin_id === $user->id;
    }
}
