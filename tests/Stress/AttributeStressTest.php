<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Stress;

use Faker\Generator as Faker;
use Rinvex\Attributes\Tests\Models\User;
use Rinvex\Attributes\Tests\TestCase;

class AttributeStressTest extends TestCase
{
    public function testOneHundredAttributes()
    {
        $faker = app()->make(Faker::class);
        // $thing->size = $faker->randomElement(['small', 'medium', 'large', 'extra large']);
        // $thing->colour = $faker->randomElement(['red', 'blue', 'green', 'yellow']);
    }
}
