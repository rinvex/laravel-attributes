<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Schema;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SuperClosure\Serializer;
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
     * The entity attribute relations.
     *
     * @var array
     */
    protected $entityAttributeRelations = [];

    /**
     * Determine if the entity attribute relations have been booted.
     *
     * @var bool
     */
    protected $entityAttributeRelationsBooted = false;

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

        if (! $this->entityAttributeRelationsBooted) {
            $attributes = $this->getEntityAttributes();

            // We will manually add a relationship for every attribute registered
            // of this entity. Once we know the relation method we have to use,
            // we will just add it to the entityAttributeRelations property.
            foreach ($attributes as $attribute) {
                $method = (bool) ($attribute->getAttributes()['is_collection'] ?? null) ? 'hasMany' : 'hasOne';

                // This will return a closure fully binded to the current entity instance,
                // which will help us to simulate any relation as if it was made in the
                // original entity class definition using a function statement.
                $relation = Closure::bind(function () use ($attribute, $method) {
                    $relation = $this->{$method}(Attribute::getTypeModel($attribute->getAttribute('type')), 'entity_id', $this->getKeyName());

                    // Since an attribute could be attached to multiple entities, then values could have
                    // same entity ID, but for different entity types, so we need to add type where
                    // clause to fetch only values related to the given entity ID + entity type.
                    $relation->where('entity_type', $this->getMorphClass());

                    // We add a where clause in order to fetch only the elements that are
                    // related to the given attribute. If no condition is set, it will
                    // fetch all the value rows related to the current entity.
                    return $relation->where($attribute->getForeignKey(), $attribute->getKey());
                }, $this, get_class($this));

                $this->setEntityAttributeRelation((string) ($attribute->getAttributes()['slug'] ?? null), $relation);
            }

            $this->entityAttributeRelationsBooted = true;
        }
    }

    /**
     * Set the given relationship on the model.
     *
     * @param string $relation
     * @param mixed  $value
     *
     * @return $this
     */
    public function relationsToArray()
    {
        $eavAttributes = [];
        $attributes = parent::relationsToArray();
        $relations = array_keys($this->getEntityAttributeRelations());

        foreach ($relations as $relation) {
            if (array_key_exists($relation, $attributes)) {
                $eavAttributes[$relation] = $this->getAttribute($relation) instanceof BaseCollection
                    ? $this->getAttribute($relation)->toArray() : $this->getAttribute($relation);

                // By unsetting the relation from the attributes array we will make
                // sure we do not provide a duplicity when adding the namespace.
                // Otherwise it would keep the relation as a key in the root.
                unset($attributes[$relation]);
            }
        }

        if (is_null($namespace = $this->getEntityAttributesNamespace())) {
            $attributes = array_merge($attributes, $eavAttributes);
        } else {
            Arr::set($attributes, $namespace, $eavAttributes);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelation($key, $value)
    {
        if ($value instanceof ValueCollection) {
            $value->link($this, $this->getEntityAttributes()->get($key));
        }

        return parent::setRelation($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationValue($key)
    {
        $value = parent::getRelationValue($key);

        // In case any relation value is found, we will just provide it as is.
        // Otherwise, we will check if exists any attribute relation for the
        // given key. If so, we will load the relation calling its method.
        if (is_null($value) && ! $this->relationLoaded($key) && $this->isEntityAttributeRelation($key)) {
            $value = $this->getRelationshipFromMethod($key);
        }

        if ($value instanceof ValueCollection) {
            $value->link($this, $this->getEntityAttributes()->get($key));
        }

        return $value;
    }

    /**
     * Get the entity attributes namespace if exists.
     *
     * @return string|null
     */
    public function getEntityAttributesNamespace(): ?string
    {
        return property_exists($this, 'entityAttributesNamespace') ? $this->entityAttributesNamespace : null;
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
     * Clear the static attributes cache for this model.
     *
     * @return void
     */
    public function clearAttributableCache()
    {
        $morphClass = $this->getMorphClass();
        if (static::$entityAttributes && static::$entityAttributes->has($morphClass)) {
            static::$entityAttributes->forget($morphClass);
        }
    }

    /**
     * Get the fillable attributes of a given array.
     *
     * @param array $attributes
     *
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
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        return $this->isEntityAttribute($key) ? $this->setEntityAttribute($key, $value) : parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->isEntityAttribute($key) ? $this->getEntityAttribute($key) : parent::getAttribute($key);
    }

    /**
     * Set the entity attribute relation.
     *
     * @param string $relation
     * @param mixed  $value
     *
     * @return $this
     */
    public function setEntityAttributeRelation(string $relation, $value)
    {
        $this->entityAttributeRelations[$relation] = $value;

        return $this;
    }

    /**
     * Check if the given key is an entity attribute relation.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isEntityAttributeRelation(string $key): bool
    {
        return isset($this->entityAttributeRelations[$key]);
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
     * Get the entity attribute relations.
     *
     * @return array
     */
    public function getEntityAttributeRelations(): array
    {
        return $this->entityAttributeRelations;
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
        $key = $this->getEntityAttributeName($key);

        return $this->getEntityAttributes()->has($key);
    }

    /**
     * Get the entity attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getEntityAttribute(string $key)
    {
        if ($this->isRawEntityAttribute($key)) {
            return $this->getEntityAttributeRelation($key);
        }

        return $this->getEntityAttributeValue($key);
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
        $key = $this->getEntityAttributeName($key);

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
                $this->setRelation($key, $current = new ValueCollection());
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
     * Determine if the given key is a raw entity attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isRawEntityAttribute(string $key): bool
    {
        return (bool) preg_match('/^raw(\w+)object$/i', $key);
    }

    /**
     * Get entity attribute bare name.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getEntityAttributeName(string $key): string
    {
        return $this->isRawEntityAttribute($key) ? Str::camel(str_ireplace(['raw', 'object'], ['', ''], $key)) : $key;
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

    /**
     * Dynamically pipe calls to attribute relations.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->isEntityAttributeRelation($method)) {
            $relation = $this->entityAttributeRelations[$method] instanceof Closure
                ? $this->entityAttributeRelations[$method]
                : (new Serializer())->unserialize($this->entityAttributeRelations[$method]);

            return call_user_func_array($relation, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        if ($this->entityAttributeRelations && current($this->entityAttributeRelations) instanceof Closure) {
            $relations = $this->entityAttributeRelations;
            $this->entityAttributeRelations = [];

            foreach ($relations as $key => $value) {
                if ($value instanceof Closure) {
                    $this->setEntityAttributeRelation($key, (new Serializer())->serialize($value));
                }
            }
        }

        return array_keys(get_object_vars($this));
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();

        if ($this->entityAttributeRelations && is_string(current($this->entityAttributeRelations))) {
            $relations = $this->entityAttributeRelations;
            $this->entityAttributeRelations = [];

            foreach ($relations as $key => $value) {
                if (is_string($value)) {
                    $this->setEntityAttributeRelation($key, (new Serializer())->unserialize($value));
                }
            }
        }
    }
}
