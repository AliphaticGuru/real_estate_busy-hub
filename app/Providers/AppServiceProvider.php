<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\VerificationCheck;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Password::defaults(function (): Password {
            $password = Password::min(10)
                ->letters()
                ->mixedCase()
                ->numbers();

            if ($this->app->environment('production')) {
                $password->uncompromised();
            }

            return $password;
        });

        Relation::morphMap([
            'listing' => Listing::class,
            'company' => Company::class,
            'professional' => Professional::class,
            'verification_check' => VerificationCheck::class,
        ]);
    }
}
