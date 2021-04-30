<?php

namespace Rinvex\Attributes\Relations;

use Closure;
use Cortex\Attributes\Models\Attribute;
use Illuminate\Database\Eloquent\Model;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Determine if we should eager load EAV relations.
     *
     * @var boolean
     */
    protected $eagerEav = false;

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model[]|static[]
     */
    public function getModels($columns = ['*'])
    {
        return $this->model::hydrate($this->queryResults($columns)->all())->all();
    }

    protected $singletonClone;
    protected function singletonClone()
    {
        return $this->singletonClone ?? $this->singletonClone = clone $this;
    }

    protected $queryResults;
    protected function queryResults($columns)
    {
        $index = md5(json_encode($columns));

        return $this->queryResults[$index] ?? $this->queryResults[$index] = $this->query->get($columns);
    }

    public function eagerLoadRelationsDDD(array $models)
    {
        // 1. Check if we need to eager load EAV
        if (array_key_exists('eav', $this->eagerLoad)) {
            $this->eagerEav = true;
            $this->without('eav');

            // 2. Fetch entity attributes
            if (array_key_exists('entityAttributes', $this->eagerLoad)) {
                $models = $this->eagerLoadRelation($models, 'entityAttributes', $this->eagerLoad['entityAttributes']);
                $entityAttributes = $models[0]->entityAttributes;
                dd($models);
                $this->without('entityAttributes');
            } else {
                $queryResults = $this->queryResults(['*'])->first();
                $entityAttributes = $this->model->newFromBuilder($queryResults)->entityAttributes;
            }

            // 3. Add dynamic relationships
            foreach ($entityAttributes as $attribute) {
                $relationName = $attribute->getAttribute('slug');
                $relationClosure = function (Model $model) use ($attribute) {
                    // Determine the relationship type, single value or collection
                    $method = $attribute->is_collection ? 'hasMany' : 'hasOne';

                    // Build a relationship between the given model and attribute
                    $relation = $model->{$method}(Attribute::getTypeModel($attribute->getAttribute('type')), 'entity_id', $model->getKeyName());

                    // Since an attribute could be attached to multiple entities, then values could have
                    // same entity ID, but for different entity types, so we need to add type where
                    // clause to fetch only values related to the given entity ID & entity type.
                    $relation->where('entity_type', $model->getMorphClass());

                    // We add a where clause in order to fetch only the elements that are
                    // related to the given attribute. If no condition is set, it will
                    // fetch all the value rows related to the current entity.
                    return $relation->where($attribute->getForeignKey(), $attribute->getKey());
                };

                $this->model::resolveRelationUsing($relationName, $relationClosure);

                dd($this->parseWithRelations([ 10 => $name]));
                $models = $this->eagerLoadRelation($models, $name, $this->parseWithRelations([ 10 => $name]));
            }
        }

        foreach ($this->eagerLoad as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (strpos($name, '.') === false) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array  $models
     * @param  string  $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        // 1. Get eager relation ready!
        $initRelation = $relation->initRelation($models, $name); // Ex. User model instance
        $eagerRelation = $relation->getEager(); // Ex. entityAttributes collection

        // 2. Match relation
        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match($initRelation, $eagerRelation, $name);
    }

}
