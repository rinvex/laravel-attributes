<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Models\Type;

use Rinvex\Attributes\Models\Value;

/**
 * Rinvex\Attributes\Models\Type\Varchar.
 *
 * @property int                                                $id
 * @property string                                             $content
 * @property int                                                $attribute_id
 * @property int                                                $entity_id
 * @property string                                             $entity_type
 * @property \Carbon\Carbon                                     $created_at
 * @property \Carbon\Carbon                                     $updated_at
 * @property-read \Rinvex\Attributes\Models\Attribute         $attribute
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $entity
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Type\Varchar whereUpdatedAt($value)
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

        $this->setTable(config('rinvex.attributes.tables.attribute_varchar_values'));
        $this->setRules([
            'content' => 'required|string|max:150',
            'attribute_id' => 'required|integer|exists:'.config('rinvex.attributes.tables.attributes').',id',
            'entity_id' => 'required|integer',
            'entity_type' => 'required|string',
        ]);
    }
}
