<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Stubs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Attributes\Tests\Factories\UserFactory;
use Rinvex\Attributes\Traits\Attributable;

class User extends Model
{
    use Attributable, HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
