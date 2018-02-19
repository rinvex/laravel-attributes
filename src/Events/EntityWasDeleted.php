<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Events;

use Rinvex\Attributes\Models\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model as Entity;

class EntityWasDeleted
{
    /**
     * Handle the entity deletion.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @return void
     */
    public function handle(Entity $entity): void
    {
        // We will initially check if the model is using soft deletes. If so,
        // the attribute values will remain untouched as they should sill
        // be available till the entity is truly deleted from database.
        if (in_array(SoftDeletes::class, class_uses_recursive(get_class($entity))) && ! $entity->isForceDeleting()) {
            return;
        }

        foreach ($entity->getEntityAttributes() as $attribute) {
            if ($entity->relationLoaded($relation = $attribute->getAttribute('name'))
                && ($values = $entity->getRelationValue($relation)) && ! $values->isEmpty()) {
                // Calling the `destroy` method from the given $type model class name
                // will finally delete the records from database if any was found.
                // We'll just provide an array containing the ids to be deleted.
                forward_static_call_array([Attribute::getTypeModel($attribute->getAttribute('type')), 'destroy'], [$values->pluck('id')->toArray()]);
            }
        }
    }
}
