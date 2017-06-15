<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\DB;
use Spatie\EloquentSortable\Sortable;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Rinvex\Attributable\Models\Attribute.
 *
 * @property int                                 $id
 * @property string                              $slug
 * @property array                               $name
 * @property array                               $description
 * @property int                                 $order
 * @property string                              $group
 * @property string                              $type
 * @property bool                                $is_required
 * @property bool                                $is_collection
 * @property string                              $default
 * @property \Carbon\Carbon                      $created_at
 * @property \Carbon\Carbon                      $updated_at
 * @property \Illuminate\Support\Collection|null $entities
 *
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute ordered($direction = 'asc')
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereGroup($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereIsCollection($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereIsRequired($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereOrder($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Attributable\Models\Attribute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Attribute extends Model implements Sortable
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
    protected $observables = ['validating', 'validated'];

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
    public $sortable = ['order_column_name' => 'sort_order'];

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

        $this->setTable(config('rinvex.attributable.tables.attributes'));
        $this->setRules([
            'name' => 'required|string|max:250',
            'type' => 'required|string|max:250',
            'description' => 'nullable|string',
            'slug' => 'required|alpha_dash|max:250|unique:'.config('rinvex.attributable.tables.attributes').',slug',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        // Auto generate slugs early before validation
        static::registerModelEvent('validating', function (self $attribute) {
            if (! $attribute->slug) {
                if ($attribute->exists && $attribute->getSlugOptions()->generateSlugsOnUpdate) {
                    $attribute->generateSlugOnUpdate();
                } elseif (! $attribute->exists && $attribute->getSlugOptions()->generateSlugsOnCreate) {
                    $attribute->generateSlugOnCreate();
                }
            }
        });
    }

    /**
     * Set the translatable name attribute.
     *
     * @param string $value
     *
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode(! is_array($value) ? [app()->getLocale() => $value] : $value);
    }

    /**
     * Set the translatable description attribute.
     *
     * @param string $value
     *
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = ! empty($value) ? json_encode(! is_array($value) ? [app()->getLocale() => $value] : $value) : null;
    }

    /**
     * Enforce clean slugs.
     *
     * @param string $value
     *
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_slug($value, '_');
    }

    /**
     * Get the entities attached to this attribute.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getEntitiesAttribute()
    {
        return DB::table(config('rinvex.attributable.tables.attribute_entity'))->where('attribute_id', $this->getKey())->get()->pluck('entity_type')->toArray();
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
            $values = [];
            foreach ($entities as $entity) {
                $values[] = ['attribute_id' => $model->id, 'entity_type' => $entity];
            }

            DB::table(config('rinvex.attributable.tables.attribute_entity'))->where('attribute_id', $model->id)->delete();
            DB::table(config('rinvex.attributable.tables.attribute_entity'))->insert($values);
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
}
