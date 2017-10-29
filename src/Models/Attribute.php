<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Models;

use Spatie\Sluggable\SlugOptions;
use Rinvex\Support\Traits\HasSlug;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Illuminate\Database\Eloquent\Builder;
use Rinvex\Support\Traits\HasTranslations;
use Rinvex\Support\Traits\ValidatingTrait;
use Spatie\EloquentSortable\SortableTrait;
use Rinvex\Attributes\Contracts\AttributeContract;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rinvex\Attributes\Models\Attribute.
 *
 * @property int                                                                               $id
 * @property string                                                                            $slug
 * @property array                                                                             $name
 * @property array                                                                             $description
 * @property int                                                                               $sort_order
 * @property string                                                                            $group
 * @property string                                                                            $type
 * @property bool                                                                              $is_required
 * @property bool                                                                              $is_collection
 * @property string                                                                            $default
 * @property \Carbon\Carbon|null                                                               $created_at
 * @property \Carbon\Carbon|null                                                               $updated_at
 * @property array                                                                             $entities
 * @property-read \Rinvex\Attributes\Support\ValueCollection|\Rinvex\Attributes\Models\Value[] $values
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereIsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributes\Models\Attribute withGroup($group)
 * @mixin \Eloquent
 */
class Attribute extends Model implements AttributeContract, Sortable
{
    use HasSlug;
    use SortableTrait;
    use HasTranslations;
    use ValidatingTrait;
    use CacheableEloquent;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'group',
        'type',
        'entities',
        'is_required',
        'is_collection',
        'default',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug' => 'string',
        'sort_order' => 'integer',
        'group' => 'string',
        'type' => 'string',
        'is_required' => 'boolean',
        'is_collection' => 'boolean',
        'default' => 'string',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * {@inheritdoc}
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * {@inheritdoc}
     */
    public $sortable = [
        'order_column_name' => 'sort_order',
    ];

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
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('rinvex.attributes.tables.attributes'));
        $this->setRules([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:10000',
            'slug' => 'required|alpha_dash|max:150|unique:'.config('rinvex.attributes.tables.attributes').',slug',
            'sort_order' => 'nullable|integer|max:10000000',
            'group' => 'nullable|string|max:150',
            'type' => 'required|string|max:150',
            'is_required' => 'sometimes|boolean',
            'is_collection' => 'sometimes|boolean',
            'default' => 'nullable|string|max:10000',
        ]);
    }

    /**
     * Enforce clean groups.
     *
     * @param string $value
     *
     * @return void
     */
    public function setGroupAttribute($value)
    {
        $this->attributes['group'] = str_slug($value);
    }

    /**
     * Access entities relation and retrieve entity types as an array,
     * Accessors/Mutators preceeds relation value when called dynamically.
     *
     * @return array
     */
    public function getEntitiesAttribute(): array
    {
        return $this->entities()->pluck('entity_type')->toArray();
    }

    /**
     * Set the attribute attached entities.
     *
     * @param \Illuminate\Support\Collection|array $value
     *
     * @return void
     */
    public function setEntitiesAttribute($entities)
    {
        static::saved(function ($model) use ($entities) {
            $this->entities()->delete();
            ! $entities || $this->entities()->createMany(array_map(function ($entity) {
                return ['entity_type' => $entity];
            }, $entities));
        });
    }

    /**
     * Get the options for generating the slug.
     *
     * @return \Spatie\Sluggable\SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
                          ->doNotGenerateSlugsOnUpdate()
                          ->generateSlugsFrom('name')
                          ->saveSlugsTo('slug');
    }

    /**
     * Scope attributes by given group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGroup(Builder $builder, string $group): Builder
    {
        return $group ? $builder->where('group', $group) : $builder;
    }

    /**
     * Get the entities attached to this attribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entities(): HasMany
    {
        return $this->hasMany(config('rinvex.attributes.models.attribute_entity'), 'attribute_id', 'id');
    }

    /**
     * Get the entities attached to this attribute.
     *
     * @param string $value
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values(string $value): HasMany
    {
        return $this->hasMany($value, 'attribute_id', 'id');
    }
}
