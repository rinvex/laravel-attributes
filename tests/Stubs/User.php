<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Attributes\Traits\Attributable;
use Rinvex\Attributes\Tests\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use Attributable;
    use HasFactory;

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
