<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Attributable\Models\Attribute;
use Rinvex\Attributable\Models\Type\Text;
use Rinvex\Attributable\Models\Type\Boolean;
use Rinvex\Attributable\Models\Type\Integer;
use Rinvex\Attributable\Models\Type\Varchar;
use Rinvex\Attributable\Models\Type\Datetime;
use Rinvex\Attributable\Models\AttributeEntity;
use Rinvex\Attributable\Console\Commands\MigrateCommand;

class AttributableServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.attributable.migrate',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.attributable');

        // Bind eloquent models to IoC container
        $this->app->singleton('rinvex.attributable.attribute', function ($app) {
            return new $app['config']['rinvex.attributable.models.attribute']();
        });
        $this->app->alias('rinvex.attributable.attribute', Attribute::class);

        $this->app->singleton('rinvex.attributable.attribute_entity', function ($app) {
            return new $app['config']['rinvex.attributable.models.attribute_entity']();
        });
        $this->app->alias('rinvex.attributable.attribute_entity', AttributeEntity::class);

        // Register attributable types
        $this->app->singleton('rinvex.attributable.types', function ($app) {
            return collect();
        });

        // Register attributable entities
        $this->app->singleton('rinvex.attributable.entities', function ($app) {
            return collect();
        });

        // Register console commands
        ! $this->app->runningInConsole() || $this->registerCommands();
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

        // Load migrations
        ! $this->app->runningInConsole() || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Publish Resources
        ! $this->app->runningInConsole() || $this->publishResources();
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    protected function publishResources()
    {
        $this->publishes([realpath(__DIR__.'/../../config/config.php') => config_path('rinvex.attributable.php')], 'rinvex-attributable-config');
        $this->publishes([realpath(__DIR__.'/../../database/migrations') => database_path('migrations')], 'rinvex-attributable-migrations');
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        // Register artisan commands
        foreach ($this->commands as $key => $value) {
            $this->app->singleton($value, function ($app) use ($key) {
                return new $key();
            });
        }

        $this->commands(array_values($this->commands));
    }
}
