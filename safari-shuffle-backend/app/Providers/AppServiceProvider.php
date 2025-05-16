<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        // Explicitly define the 'api' rate limiter here
        RateLimiter::for('api', function (Request $request) {
            // Use configuration value or default to 60
            $maxAttempts = config('rate-limiter.api.max_attempts', 60);
            $decayMinutes = config('rate-limiter.api.decay_minutes', 1);
            return Limit::perMinute($maxAttempts)->by($request->user()?->id ?: $request->ip());
        });
    }
}
