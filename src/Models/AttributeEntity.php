<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Rinvex\Attributes\Contracts\AttributeEntityContract;

/**
 * Rinvex\Attributes\Models\AttributeEntity.
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
class AttributeEntity extends Model implements AttributeEntityContract
{
    use CacheableEloquent;

    /**
     * {@inheritdoc}
     */
    protected $table = 'attribute_entity';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'entity_type',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'entity_type' => 'string',
    ];
}
