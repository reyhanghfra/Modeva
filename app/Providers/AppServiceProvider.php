<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\User;
use App\Policies\PaymentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        Paginator::defaultView('vendor.pagination.admin');

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
    }
}
