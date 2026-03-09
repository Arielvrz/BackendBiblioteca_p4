<?php

namespace App\Providers;

use App\Models\Book;
use App\Policies\LibroPolicy;
use App\Models\Loan;
use App\Policies\LoanPolicy;
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
        Gate::policy(Book::class, LibroPolicy::class);
        Gate::policy(Loan::class, LoanPolicy::class);
    }
}
