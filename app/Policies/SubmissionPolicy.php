<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission)
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($submission->user_id === $user->id) {
            return true;
        }
        $task = $submission->task;
        $class = $task->classModel;

        return $class->admin_id === $user->id;
    }

    public function create(User $user, $task)
    {
        // only students enrolled
        if ($user->role !== 'mahasiswa') {
            return false;
        }

        return $task->classModel->members()->where('user_id', $user->id)->exists();
    }

    public function grade(User $user, Submission $submission)
    {
        $task = $submission->task;
        $class = $task->classModel;

        return $user->role === 'admin' || $class->admin_id === $user->id;
    }
}
