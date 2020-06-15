<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Rinvex\Attributes\Support\ValueCollection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class Value extends Model
{
    use ValidatingTrait;

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
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id', 'attribute');
    }

    /**
     * Polymorphic relationship to the entity instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id', 'id');
    }

    /**
     * Check if value should push to relations when saving.
     *
     * @return bool
     */
    public function shouldPush(): bool
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
