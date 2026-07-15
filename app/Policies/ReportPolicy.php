<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user, Report $report)
    {
        // Only class admin or global admin can view reports
        if ($user->role === 'admin') {
            return true;
        }

        return $report->class_id && $report->class->admin_id === $user->id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }
}
