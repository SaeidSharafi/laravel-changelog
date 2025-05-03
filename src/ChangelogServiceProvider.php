<?php

namespace SaeidSharafi\Changelog;

use Illuminate\Support\ServiceProvider;
use SaeidSharafi\Changelog\Console\TestCommand;

class ChangelogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/changelog.php' => config_path('changelog.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations')
            ], 'migrations');

            // Register the changelog entry command
            $this->commands([
                \SaeidSharafi\Changelog\Console\MakeChangelogEntryCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/changelog.php', 'changelog');

        // Register the main class to use with the facade
        $this->app->singleton('changelog', fn () => new Changelog());
    }
}
