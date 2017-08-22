<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;

/**
 * Rinvex\Attributable\Models\AttributeEntity.
 *
 * @property int            $attribute_id
 * @property string         $entity_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\AttributeEntity whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\AttributeEntity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\AttributeEntity whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\AttributeEntity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AttributeEntity extends Model
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
