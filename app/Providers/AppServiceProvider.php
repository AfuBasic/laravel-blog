<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Services\LLMService;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind('llm', LLMService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        DB::listen(function ($query) {
            logger("[SQL] " . $query->sql);
            logger("[Bindings] " . json_encode($query->bindings));
            logger("[Time] " . $query->time . "ms");
        });
    }
}
