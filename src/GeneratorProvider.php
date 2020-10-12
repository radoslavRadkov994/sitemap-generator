<?php

namespace R3F\SitemapGenerator;

use Illuminate\Support\ServiceProvider;
use R3F\SitemapGenerator\Commands\SitemapGenerator;

class GeneratorProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/configs/sitemap-generator.php' => config_path('sitemap-generator.php'),
        ]);
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
