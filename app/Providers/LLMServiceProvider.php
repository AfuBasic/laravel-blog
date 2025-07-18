<?php

namespace App\Providers;

use App\Services\LLMService;
use Illuminate\Support\ServiceProvider;

class LLMServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LLMService::class, function ($app) {
            return new \App\Services\LLMService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
