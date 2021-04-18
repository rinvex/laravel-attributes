<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Schema;
use Rinvex\Attributes\Models\Value;
use Rinvex\Attributes\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rinvex\Attributes\Events\EntityWasSaved;
use Rinvex\Attributes\Scopes\EagerLoadScope;
use Rinvex\Attributes\Events\EntityWasDeleted;
use Rinvex\Attributes\Support\ValueCollection;
use Illuminate\Support\Collection as BaseCollection;

trait Attributable
{
    /**
     * The entity attributes.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected static $entityAttributes;

    /**
     * The entity attribute value trash.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $entityAttributeValueTrash;

    /**
     * Boot the attributable trait for a model.
     *
     * @return void
     */
    public static function bootAttributable()
    {
        static::addGlobalScope(new EagerLoadScope());
        static::saved(EntityWasSaved::class.'@handle');
        static::deleted(EntityWasDeleted::class.'@handle');
    }

    /**
     * Check if the model needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        parent::bootIfNotBooted();

        // Dynamically add a relationship for every attribute registered of this entity.
        foreach ($this->getEntityAttributes() as $attribute) {
            self::resolveRelationUsing($attribute->getAttribute('slug'), function () use ($attribute) {
                // This will return a closure fully binded to the current entity instance,
                // which will help us to simulate any relation as if it was made in the
                // original entity class definition using a function statement.
                $method = $attribute->is_collection ? 'hasMany' : 'hasOne';
                $relation = $this->{$method}(Attribute::getTypeModel($attribute->getAttribute('type')), 'entity_id', $this->getKeyName());

                // Since an attribute could be attached to multiple entities, then values could have
                // same entity ID, but for different entity types, so we need to add type where
                // clause to fetch only values related to the given entity ID + entity type.
                $relation->where('entity_type', $this->getMorphClass());

                // We add a where clause in order to fetch only the elements that are
                // related to the given attribute. If no condition is set, it will
                // fetch all the value rows related to the current entity.
                return $relation->where($attribute->getForeignKey(), $attribute->getKey());
            });
        }
    }

    /**
     * Get the entity attributes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEntityAttributes(): Collection
    {
        $morphClass = $this->getMorphClass();
        static::$entityAttributes = static::$entityAttributes ?? collect();

        if (! static::$entityAttributes->has($morphClass) && Schema::hasTable(config('rinvex.attributes.tables.attribute_entity'))) {
            $locale = app()->getLocale();

            /* This is a trial to implement per resource attributes,
               it's working but I don't like current implementation.
            $routeParam = request()->route($morphClass);

            // @TODO: This is REALLY REALLY BAD DESIGN!! But can't figure out a better way for now!!
            // Refactor required, we need to catch `$this` itself, we should NOT use request and routes here!!
            // But still at this very early stage, `$this` still not bound to model's data, so it's just empty object!
            $entityId = $routeParam && collect(class_uses_recursive(static::class))->contains(HashidsTrait::class) && ! is_numeric($routeParam)
                ? optional(Hashids::decode($routeParam))[0] : $routeParam;

            $attributes = app('rinvex.attributes.attribute_entity')->where('entity_type', $morphClass)->where('entity_id', $entityId)->get()->pluck('attribute_id');
             */

            $attributes = app('rinvex.attributes.attribute_entity')->where('entity_type', $morphClass)->get()->pluck('attribute_id');
            static::$entityAttributes->put($morphClass, app('rinvex.attributes.attribute')->whereIn('id', $attributes)->orderBy('sort_order', 'ASC')->orderBy("name->\${$locale}", 'ASC')->get()->keyBy('slug'));
        }

        return static::$entityAttributes->get($morphClass) ?? new Collection();
    }

    /**
     * Get the fillable attributes of a given array.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        foreach (array_diff_key($attributes, array_flip($this->getFillable())) as $key => $value) {
            if ($this->isEntityAttribute($key)) {
                $this->setEntityAttribute($key, $value);
            }
        }

        if (count($this->getFillable()) > 0 && ! static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }

        return $attributes;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        return $this->isEntityAttribute($key) ? $this->setEntityAttribute($key, $value) : parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->isEntityAttribute($key) ? $this->getEntityAttributeValue($key) : parent::getAttribute($key);
    }

    /**
     * Get the entity attribute value trash.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEntityAttributeValueTrash(): BaseCollection
    {
        return $this->entityAttributeValueTrash ?: $this->entityAttributeValueTrash = collect([]);
    }

    /**
     * Check if the given key is an entity attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function isEntityAttribute(string $key)
    {
        return $this->getEntityAttributes()->has($key);
    }

    /**
     * Get the entity attribute value.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getEntityAttributeValue(string $key)
    {
        $value = $this->getEntityAttributeRelation($key);

        // In case we are accessing to a multivalued attribute, we will return
        // a collection with pairs of id and value content. Otherwise we'll
        // just return the single model value content as a plain result.
        if ($this->getEntityAttributes()->get($key)->is_collection) {
            return $value->pluck('content');
        }

        return ! is_null($value) ? $value->getAttribute('content') : null;
    }

    /**
     * Get the entity attribute relationship.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getEntityAttributeRelation(string $key)
    {
        if ($this->relationLoaded($key)) {
            return $this->getRelation($key);
        }

        return $this->getRelationValue($key);
    }

    /**
     * Set the entity attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setEntityAttribute(string $key, $value)
    {
        $current = $this->getEntityAttributeRelation($key);
        $attribute = $this->getEntityAttributes()->get($key);

        // $current will always contain a collection when an attribute is multivalued
        // as morphMany provides collections even if no values were matched, making
        // us assume at least an empty collection object will be always provided.
        if ($attribute->is_collection) {
            if (is_null($current)) {
                $this->setRelation($key, $current = new ValueCollection([], $this, $attribute));
            }

            $current->replace($value);

            return $this;
        }

        // If the attribute to set is a collection, it will be replaced by the
        // new value. If the value model does not exist, we will just create
        // and set a new value model, otherwise its value will get updated.
        if (is_null($current)) {
            return $this->setEntityAttributeValue($attribute, $value);
        }

        if ($value instanceof Value) {
            $value = $value->getAttribute('content');
        }

        $current->setAttribute('entity_type', $this->getMorphClass());

        return $current->setAttribute('content', $value);
    }

    /**
     * Set the entity attribute value.
     *
     * @param \Rinvex\Attributes\Models\Attribute $attribute
     * @param mixed                               $value
     *
     * @return $this
     */
    protected function setEntityAttributeValue(Attribute $attribute, $value)
    {
        if (! is_null($value) && ! $value instanceof Value) {
            $model = Attribute::getTypeModel($attribute->getAttribute('type'));
            $instance = new $model();

            $instance->setAttribute('entity_id', $this->getKey());
            $instance->setAttribute('entity_type', $this->getMorphClass());
            $instance->setAttribute($attribute->getForeignKey(), $attribute->getKey());
            $instance->setAttribute('content', $value);

            $value = $instance;
        }

        return $this->setRelation($attribute->getAttribute('slug'), $value);
    }

    /**
     * Get the attributes attached to this entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function attributes(): ?Collection
    {
        return $this->getEntityAttributes();
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
