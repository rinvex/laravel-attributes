<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Attributes\Traits\Attributable;

class User extends Model
{
    use Attributable;
}
