<?php

namespace App\Providers;

use App\Models\Grade;
use App\Models\GradeTask;
use App\Observers\GradeObserver;
use App\Observers\GradeTaskObserver;
use Illuminate\Support\ServiceProvider;

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
        GradeTask::observe(GradeTaskObserver::class);
        // Grade::observe(GradeObserver::class);
    }
}
