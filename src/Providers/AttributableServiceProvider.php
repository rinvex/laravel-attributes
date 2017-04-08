<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Attributable\Models\Type\Text;
use Rinvex\Attributable\Models\Type\Boolean;
use Rinvex\Attributable\Models\Type\Integer;
use Rinvex\Attributable\Models\Type\Varchar;
use Rinvex\Attributable\Models\Type\Datetime;

class AttributableServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.attributable');

        // Register attributable types
        $this->app->singleton('rinvex.attributable.types', function ($app) {
            return collect();
        });

        // Register attributable entities
        $this->app->singleton('rinvex.attributable.entities', function ($app) {
            return collect();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Add default attributable types
        app('rinvex.attributable.types')->push(Text::class);
        app('rinvex.attributable.types')->push(Boolean::class);
        app('rinvex.attributable.types')->push(Integer::class);
        app('rinvex.attributable.types')->push(Varchar::class);
        app('rinvex.attributable.types')->push(Datetime::class);

        if ($this->app->runningInConsole()) {
            // Load migrations
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

            // Publish Resources
            $this->publishResources();
        }
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    protected function publishResources()
    {
        // Publish config
        $this->publishes([
            realpath(__DIR__.'/../../config/config.php') => config_path('rinvex.attributable.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            realpath(__DIR__.'/../../database/migrations') => database_path('migrations'),
        ], 'migrations');
    }
}
