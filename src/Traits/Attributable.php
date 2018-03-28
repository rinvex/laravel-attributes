<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Schema;
use Closure;
use Illuminate\Support\Arr;
use SuperClosure\Serializer;
use Rinvex\Attributes\Models\Value;
use Rinvex\Attributes\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rinvex\Attributes\Events\EntityWasSaved;
use Rinvex\Attributes\Scopes\EagerLoadScope;
use Rinvex\Attributes\Events\EntityWasDeleted;
use Rinvex\Attributes\Support\RelationBuilder;
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
     * {@inheritdoc}
     */
    protected function bootIfNotBooted()
    {
        parent::bootIfNotBooted();

        if (! $this->entityAttributeRelationsBooted) {
            app(RelationBuilder::class)->build($this);

            $this->entityAttributeRelationsBooted = true;
        }
    }

    /**
     * {@inheritdoc}
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
            $attributes = app('rinvex.attributes.attribute_entity')->where('entity_type', $morphClass)->get()->pluck('attribute_id');
            static::$entityAttributes->put($morphClass, app('rinvex.attributes.attribute')->whereIn('id', $attributes)->orderBy('sort_order', 'ASC')->orderBy("name->\${$locale}", 'ASC')->get()->keyBy('name'));
        }

        return static::$entityAttributes->get($morphClass) ?? new Collection();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        return $this->isEntityAttribute($key) ? $this->setEntityAttribute($key, $value) : parent::setAttribute($key, $value);
    }

    /**
     * {@inheritdoc}
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

        return $this->setRelation($attribute->getAttribute('name'), $value);
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
        return $this->isRawEntityAttribute($key) ? camel_case(str_ireplace(['raw', 'object'], ['', ''], $key)) : $key;
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
