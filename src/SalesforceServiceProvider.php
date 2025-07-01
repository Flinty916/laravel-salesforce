<?php

namespace Flinty916\LaravelSalesforce;

use Flinty916\LaravelSalesforce\Commands\GenerateSalesforceObjects;
use Illuminate\Support\ServiceProvider;

class SalesforceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/salesforce.php', 'salesforce');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/salesforce.php' => base_path('config/salesforce.php'),
        ], 'config');
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSalesforceObjects::class
            ]);
        }
    }
}
