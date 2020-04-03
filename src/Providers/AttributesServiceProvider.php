<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Attributes\Models\Attribute;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Attributes\Models\AttributeEntity;
use Rinvex\Attributes\Console\Commands\MigrateCommand;
use Rinvex\Attributes\Console\Commands\PublishCommand;
use Rinvex\Attributes\Console\Commands\RollbackCommand;

class AttributesServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.attributes.migrate',
        PublishCommand::class => 'command.rinvex.attributes.publish',
        RollbackCommand::class => 'command.rinvex.attributes.rollback',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.attributes');

        // Bind eloquent models to IoC container
        $this->app->singleton('rinvex.attributes.attribute', $attributeyModel = $this->app['config']['rinvex.attributes.models.attribute']);
        $attributeyModel === Attribute::class || $this->app->alias('rinvex.attributes.attribute', Attribute::class);

        $this->app->singleton('rinvex.attributes.attribute_entity', $attributeEntityModel = $this->app['config']['rinvex.attributes.models.attribute_entity']);
        $attributeEntityModel === AttributeEntity::class || $this->app->alias('rinvex.attributes.attribute_entity', AttributeEntity::class);

        // Register attributes entities
        $this->app->singleton('rinvex.attributes.entities', function ($app) {
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
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-attributes');
        $this->publishesMigrations('rinvex/laravel-attributes');
        ! $this->autoloadMigrations('rinvex/attributes') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
