<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\Job;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('create-job', function (User $user) {
            return $user->role === 'employer' || $user->role === 'admin';
        });

        Gate::define('update-job', function (User $user, Job $job) {
            return $user->role === 'employer' && $user->id === $job->employer_id || $user->role === 'admin';
        });
        Gate::define('update-job-status', function ($user, Job $job) {
            if ($user->role === 'admin') {
                return true;
            }

            return $user->id === $job->employer_id &&
                   in_array(request('status'), [Job::STATUS_OPEN, Job::STATUS_CLOSED]);
        });
        Gate::define('delete-job', function (User $user, Job $job) {
            return $user->role === 'employer' && $user->id === $job->employer_id || $user->role === 'admin';
        });

        Gate::define('view-job', function (User $user = null, Job $job) {
            return true;
        });

        // Admin can do anything
        Gate::before(function (User $user) {
            if ($user->role === 'admin') {
                return true;
            }
        });
    }
}
