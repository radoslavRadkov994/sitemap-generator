<?php

namespace R3F\SitemapGenerator;

use Illuminate\Support\ServiceProvider;
use R3F\SitemapGenerator\Commands\SitemapGenerator;

class GeneratorProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SitemapGenerator::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/views', 'SitemapGenerator');
    }

    public function register()
    {
        // ...
    }
}
