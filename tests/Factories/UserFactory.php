<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rinvex\Attributes\Tests\Stubs\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'foobarbaz',
        ];
    }
}
