<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
            $entityAttributes = static::entityAttributes();

            // If there is any eager loading with the name 'eav', we will replace it with
            // all the registered properties for the entity. We'll simulate as if the
            // user manually added all of these attributes to the $with property.
            if (array_key_exists('eav', $eagerLoads)) {
                $builder->without('eav');

                //$entityAttributes->groupBy('type')->each(function () {
                //
                //});

                // Attach dynamic relations to this model
                $entityAttributes->each(fn($attribute) => static::resolveRelationUsing($attribute->getAttribute('slug'), function (Model $model) use ($attribute) {
                    // Determine the relationship type, single value or collection
                    $method = $attribute->is_collection ? 'hasMany' : 'hasOne';

                    // Build a relationship between the given model and attribute
                    $relation = $model->{$method}($attribute->getTypeModel($attribute->getAttribute('type')), 'entity_id', $model->getKeyName());

                    // Since an attribute could be attached to multiple entities, then values could have
                    // same entity ID, but for different entity types, so we need to add type where
                    // clause to fetch only values related to the given entity ID & entity type.
                    $relation->where('entity_type', $model->getMorphClass());

                    // We add a where clause in order to fetch only the elements that are
                    // related to the given attribute. If no condition is set, it will
                    // fetch all the value rows related to the current entity.
                    return $relation->where($attribute->getForeignKey(), $attribute->getKey());
                }));

                // Call dynamic relations of this model
                $entityAttributes->pluck('slug')->each(fn ($attribute) => $builder->with($attribute));
            }
        });

        static::retrieved(function (self $model) {
            $attributes = app('rinvex.attributes.attribute')
                ->join(config('rinvex.attributes.tables.attribute_entity'), 'attributes.id', '=', 'attribute_entity.attribute_id')
                ->where('attribute_entity.entity_type', static::morphClass())
                ->get();

            $booleans = \DB::table(config('rinvex.attributes.tables.attribute_boolean_values'))->whereIn('attribute_id', $attributes->pluck('id'))->where('entity_id', $model->getKey())->where('entity_type', $model->getMorphClass());
            $datetimes = \DB::table(config('rinvex.attributes.tables.attribute_datetime_values'))->whereIn('attribute_id', $attributes->pluck('id'))->where('entity_id', $model->getKey())->where('entity_type', $model->getMorphClass());
            $integers = \DB::table(config('rinvex.attributes.tables.attribute_integer_values'))->whereIn('attribute_id', $attributes->pluck('id'))->where('entity_id', $model->getKey())->where('entity_type', $model->getMorphClass());
            $texts = \DB::table(config('rinvex.attributes.tables.attribute_text_values'))->whereIn('attribute_id', $attributes->pluck('id'))->where('entity_id', $model->getKey())->where('entity_type', $model->getMorphClass());
            $varchars = \DB::table(config('rinvex.attributes.tables.attribute_varchar_values'))->whereIn('attribute_id', $attributes->pluck('id'))->where('entity_id', $model->getKey())->where('entity_type', $model->getMorphClass());

            $records = $varchars->unionAll($texts)->unionAll($integers)->unionAll($datetimes)->unionAll($booleans)->get();
            dd($records);

            $model->setAttribute('asd', 'qwe');
            //dd('asd');
            //$model->append(['asd' => 'qwe']);
            //$model->offsetSet(['asd' => 'qwe']);
            // @TODO: implement relations saving
            //$model->testimonials()->delete();
        });
        static::saved(function (self $model) {
            // @TODO: implement relations saving
            //$model->testimonials()->delete();
        });

        static::deleted(function (self $model) {
            // @TODO: implement relations saving
            $entityAttributes = static::entityAttributes();
            $entityAttributes->each(function ($attribute) use ($model) {
                $model->{$attribute->getAttribute('type')}()->delete();
            });
        });
    }

    /**
     * Scope query with the given entity attribute.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $attribute
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasAttribute(Builder $builder, string $attribute, $value): Builder
    {
        return $builder->whereHas($attribute, function (Builder $builder) use ($value) {
            $builder->where('content', $value)->where('entity_type', $this->getMorphClass());
        });
    }
}
