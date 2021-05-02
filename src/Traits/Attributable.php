<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Rinvex\Attributes\Models\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Attributable
{
    /**
     * Entity attributes collection.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected static $entityAttributes;

    /**
     * Retrieve entity attributes for this model.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected static function entityAttributes(): Collection
    {
        return static::$entityAttributes ?? static::$entityAttributes = app('rinvex.attributes.attribute')->join(config('rinvex.attributes.tables.attribute_entity'), 'attributes.id', '=', 'attribute_entity.attribute_id')->where('attribute_entity.entity_type', static::morphClass())->get();
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @notice This is just a static clone of `HasRelationships::getMorphClass`
     *
     * @return string
     */
    protected static function morphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        return static::class;
    }

    /**
     * Boot the attributable trait for a model.
     *
     * @return void
     */
    public static function bootAttributable()
    {
        // Add eav global scope
        static::addGlobalScope('eav', function (Builder $builder) {
            // Get entity attributes for this model
            $eagerLoads = $builder->getEagerLoads();
            $entityAttributes = static::entityAttributes()->groupBy('type');

            // @TODO: switch to query builder, there's no point using relations here,
            // with that much complexity, and un-ability to eager load properly without duplicating queries,
            // plus the real value was never meant to register attributes as relations, but to attach to the model. Let's keep it simple.

            // If there is any eager loading with the name 'eav', we will replace it with
            // all the registered properties for the entity. We'll simulate as if the
            // user manually added all of these attributes to the $with property.
            if (array_key_exists('eav', $eagerLoads)) {
                $builder->without('eav');
                $entityAttributes->keys()->each(fn ($type) => $builder->with('eav'.ucfirst($type)));

                $entityAttributes->each(function ($attributes, $type) {
                    static::resolveRelationUsing('eav'.ucfirst($type), function (Model $model) use ($type, $attributes) {
                        //$attributes->each(function ($attribute) use ($model) {
                        //    $model->setAttribute('asdasd', 'qweqwe');
                        //});
                        //$model->setAttribute('asdasd', 'qweqwe');
                        //dd($model);
                        //dd($model->getAttributes());
                        //$model->setAttribute('asdasd', 'qweqwe');
                        //dd('rel');
                        // Build a relationship between the given model and attribute
                        $relation = $model->hasMany(Attribute::getTypeModel($type), 'entity_id', $model->getKeyName());

                        // Since an attribute could be attached to multiple entities, then values could have
                        // same entity ID, but for different entity types, so we need to add type where
                        // clause to fetch only values related to the given entity ID & entity type.
                        $relation->where('entity_type', $model->getMorphClass());

                        // We add a where clause in order to fetch only the elements that are
                        // related to the given attribute. If no condition is set, it will
                        // fetch all the value rows related to the current entity.
                        //dd($relation->whereIn('attribute_id', $attributes->pluck('id')->all()));
                        return $relation->whereIn('attribute_id', $attributes->pluck('id')->all());
                    });

                    //$attributes->each(function ($attribute) use ($type, $typeIds) {
                    //    // Attach dynamic relations to this model
                    //    static::resolveRelationUsing($attribute->getAttribute('slug'), function (Model $model) use ($attribute) {
                    //        // Determine the relationship type, single value or collection
                    //        $method = $attribute->is_collection ? 'hasMany' : 'hasOne';
                    //
                    //        // Build a relationship between the given model and attribute
                    //        $relation = $model->{$method}($attribute->getTypeModel($attribute->getAttribute('type')), 'entity_id', $model->getKeyName());
                    //
                    //        // Since an attribute could be attached to multiple entities, then values could have
                    //        // same entity ID, but for different entity types, so we need to add type where
                    //        // clause to fetch only values related to the given entity ID & entity type.
                    //        $relation->where('entity_type', $model->getMorphClass());
                    //
                    //        // We add a where clause in order to fetch only the elements that are
                    //        // related to the given attribute. If no condition is set, it will
                    //        // fetch all the value rows related to the current entity.
                    //        return $relation->where($attribute->getForeignKey(), $attribute->getKey());
                    //    });
                    //});

                });

            }

            //foreach ($entityAttributes as $type => $attributes) {
            //    // Optimize the relationship to be one query instead of multiple in case of same attribute type
            //}



        });

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
