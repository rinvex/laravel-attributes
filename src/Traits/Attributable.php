<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Attributes\Relations\Builder;
use Rinvex\Attributes\Relations\NullableMorphToMany;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
trait Attributable
{
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Rinvex\Attributes\Relations\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    protected static $entityAttributes;
    protected static function entityAttributes()
    {
        return static::$entityAttributes ?? static::$entityAttributes = clone $this;
    }
    /**
     * Boot the attributable trait for a model.
     *
     * @return void
     */
    public static function bootAttributable()
    {
        static::saved(function (self $model) {
            // @TODO: implement relations saving
            //$model->testimonials()->delete();
        });

        static::deleted(function (self $model) {
            // @TODO: implement relations deletion
            //$model->testimonials()->delete();
        });
    }

    /**
     * Get the entity attributes.
     *
     * @return \Rinvex\Attributes\Relations\NullableMorphToMany
     */
    public function entityAttributes()
    {
        return $this->nullableMorphToMany(config('rinvex.attributes.models.attribute'), 'entity', config('rinvex.attributes.tables.attribute_entity'), 'entity_id', 'attribute_id');
    }

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  bool  $inverse
     * @return \Rinvex\Attributes\Relations\NullableMorphToMany
     */
    public function nullableMorphToMany($related, $name, $table = null, $foreignPivotKey = null,
                                $relatedPivotKey = null, $parentKey = null,
                                $relatedKey = null, $inverse = false)
    {
        $caller = $this->guessBelongsToManyRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name.'_id';

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        if (! $table) {
            $words = preg_split('/(_)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

            $lastWord = array_pop($words);

            $table = implode('', $words).Str::plural($lastWord);
        }

        return $this->newNullableMorphToMany(
            $instance->newQuery(), $this, $name, $table,
            $foreignPivotKey, $relatedPivotKey, $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(), $caller, $inverse
        );
    }

    /**
     * Instantiate a new MorphToMany relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $name
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string|null  $relationName
     * @param  bool  $inverse
     * @return \Rinvex\Attributes\Relations\NullableMorphToMany
     */
    protected function newNullableMorphToMany(BaseBuilder $query, Model $parent, $name, $table, $foreignPivotKey,
                                      $relatedPivotKey, $parentKey, $relatedKey,
                                      $relationName = null, $inverse = false)
    {
        return new NullableMorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse);
    }

    /**
     * Scope query with the given entity attribute.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $key
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasAttribute(Builder $builder, string $key, $value): Builder
    {
        return $builder->whereHas($key, function (Builder $builder) use ($value) {
            $builder->where('content', $value)->where('entity_type', $this->getMorphClass());
        });
    }
}
