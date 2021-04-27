<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Factories;

use Rinvex\Attributes\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
