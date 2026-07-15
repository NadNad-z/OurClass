<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task)
    {
        $class = $task->classModel;
        if ($user->role === 'admin') {
            return true;
        }
        if ($class->admin_id === $user->id) {
            return true;
        }

        return $class->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, $class)
    {
        return $user->role === 'admin' || $class->admin_id === $user->id;
    }

    public function manage(User $user, Task $task)
    {
        $class = $task->classModel;

        return $user->role === 'admin' || $class->admin_id === $user->id;
    }
}
