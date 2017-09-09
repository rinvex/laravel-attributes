<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Contracts;

/**
 * Rinvex\Attributes\Contracts\AttributeEntityContract.
 *
 * @property int            $attribute_id
 * @property string         $entity_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
interface AttributeEntityContract
{
    //
}
