<?php

namespace App\Providers;

use App\Models\ClassModel;
use App\Models\Report;
use App\Models\Submission;
use App\Models\Task;
use App\Policies\ClassModelPolicy;
use App\Policies\ReportPolicy;
use App\Policies\SubmissionPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        ClassModel::class => ClassModelPolicy::class,
        Task::class => TaskPolicy::class,
        Submission::class => SubmissionPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
