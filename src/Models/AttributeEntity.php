<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Rinvex\Attributes\Models\AttributeEntity.
 *
 * @property int                 $attribute_id
 * @property string              $entity_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\AttributeEntity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AttributeEntity extends Model
{
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
