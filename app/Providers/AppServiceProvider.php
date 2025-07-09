<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AtikMerkeziService;
use App\Services\SearchService;
use App\Services\LocationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service'leri singleton olarak kaydet
        $this->app->singleton(AtikMerkeziService::class, function ($app) {
            return new AtikMerkeziService();
        });

        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService();
        });

        $this->app->singleton(LocationService::class, function ($app) {
            return new LocationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // API rate limiting ve diğer boot işlemleri burada yapılabilir
        
        // Development ortamında SQL sorguları loglanabilir
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info('SQL Query: ' . $query->sql, [
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms'
                ]);
            });
        }
    }
}
