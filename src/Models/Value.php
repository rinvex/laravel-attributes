<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Models;

use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Rinvex\Attributable\Support\ValueCollection;

abstract class Value extends Model
{
    use ValidatingTrait;
    use CacheableEloquent;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'content',
        'attribute_id',
        'entity_id',
        'entity_type',
    ];

    /**
     * Determine if value should push to relations when saving.
     *
     * @var bool
     */
    protected $shouldPush = false;

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Relationship to the attribute entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }

    /**
     * Polymorphic relationship to the entity instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Check if value should push to relations when saving.
     *
     * @return bool
     */
    public function shouldPush()
    {
        return $this->shouldPush;
    }

    /**
     * {@inheritdoc}
     */
    public function newCollection(array $models = [])
    {
        return new ValueCollection($models);
    }
}
