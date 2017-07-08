<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Models\Type;

use Rinvex\Attributable\Models\Value;

/**
 * Rinvex\Attributable\Models\Type\Varchar.
 *
 * @property int $id
 * @property string $content
 * @property int $attribute_id
 * @property int $entity_id
 * @property string $entity_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Rinvex\Attributable\Models\Attribute $attribute
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $entity
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Type\Varchar whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Varchar extends Value
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'content' => 'string',
        'attribute_id' => 'integer',
        'entity_id' => 'integer',
        'entity_type' => 'string',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('rinvex.attributable.tables.values_varchar'));
        $this->setRules([
            'content' => 'required|string|max:150',
            'attribute_id' => 'required|integer|exists:'.config('rinvex.attributable.tables.attributes').',id',
            'entity_id' => 'required|integer',
            'entity_type' => 'required|string',
        ]);
    }
}
