<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Stress;

use Faker\Generator as Faker;
use Rinvex\Attributes\Models\Attribute;
use Rinvex\Attributes\Tests\Models\Thing;
use Rinvex\Attributes\Tests\Models\User;
use Rinvex\Attributes\Tests\TestCase;

class AttributeStressTest extends TestCase
{
    /**
     * Create ten attributes.
     *
     * @return void
     */
    public function testTenAttributes()
    {
        // TODO: Fix the issue with attaching entities to attributes so that we can use the factory instead.
        // factory(Attribute::class, 10)->create(['entities' => [Thing::class, User::class]])->each(function ($attribute) {
        //     // ...
        // });
        $faker = app()->make(Faker::class);
        for ($i = 0; $i < 10; $i++) {
            $attributes[] = $faker->unique()->slug(2);
        }
        foreach ($attributes as $attribute) {
            app('rinvex.attributes.attribute')->create([
                'slug' => $attribute,
                'type' => $faker->randomElement(['boolean', 'datetime', 'integer', 'text', 'varchar']),
                'name' => 'Thing ' . ucfirst($attribute),
                'entities' => [Thing::class, User::class],
            ]);
        }

        $this->assertDatabaseCount('attributes', 10);
    }

    /**
     * Create one hundred attributes.
     *
     * @return void
     */
    public function testOneHundredAttributes()
    {
        $faker = app()->make(Faker::class);
        for ($i = 0; $i < 100; $i++) {
            $attributes[] = $faker->unique()->slug(2);
        }
        foreach ($attributes as $attribute) {
            app('rinvex.attributes.attribute')->create([
                'slug' => $attribute,
                'type' => $faker->randomElement(['boolean', 'datetime', 'integer', 'text', 'varchar']),
                'name' => 'Thing ' . ucfirst($attribute),
                'entities' => [Thing::class, User::class],
            ]);
        }

        $this->assertDatabaseCount('attributes', 100);
    }
}
