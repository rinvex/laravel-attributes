<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Events;

use Exception;
use Rinvex\Attributes\Models\Value;
use Rinvex\Attributes\Support\ValueCollection;
use Illuminate\Database\Eloquent\Model as Entity;

class EntityWasSaved
{
    /**
     * The trash collection.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $trash;

    /**
     * Save values when an entity is saved.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle(Entity $entity)
    {
        $this->trash = $entity->getEntityAttributeValueTrash();

        // Wrap the whole process inside database transaction
        $connection = $entity->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($entity->getEntityAttributes() as $attribute) {
                if ($entity->relationLoaded($relation = $attribute->getAttribute('slug'))) {
                    $relationValue = $entity->getRelationValue($relation);

                    if ($relationValue instanceof ValueCollection) {
                        foreach ($relationValue as $value) {
                            // Set attribute value's entity_id since it's always null,
                            // because when RelationBuilder::build is called very early
                            $value->setAttribute('entity_id', $entity->getKey());
                            $this->saveOrTrashValue($value);
                        }
                    } elseif (! is_null($relationValue)) {
                        // Set attribute value's entity_id since it's always null,
                        // because when RelationBuilder::build is called very early
                        $relationValue->setAttribute('entity_id', $entity->getKey());
                        $this->saveOrTrashValue($relationValue);
                    }
                }
            }

            if ($this->trash->count()) {
                // Fetch the first item's class to know the model used for deletion
                $class = get_class($this->trash->first());

                // Let's batch delete all the values based on their ids
                $class::whereIn('id', $this->trash->pluck('id'))->delete();

                // Now, empty the trash
                $this->trash = collect([]);
            }
        } catch (Exception $e) {
            // Rollback transaction on failure
            $connection->rollBack();

            throw $e;
        }

        // Commit transaction on success
        $connection->commit();
    }

    /**
     * Save or trash the given value according to it's content.
     *
     * @param \Rinvex\Attributes\Models\Value $value
     *
     * @return void
     */
    protected function saveOrTrashValue(Value $value)
    {
        // In order to provide flexibility and let the values have their own
        // relationships, here we'll check if a value should be completely
        // saved with its relations or just save its own current state.
        if (! is_null($value) && ! $this->trashValue($value)) {
            if ($value->shouldPush()) {
                $value->push();
            } else {
                $value->save();
            }
        }
    }

    /**
     * Trash the given value.
     *
     * @param \Rinvex\Attributes\Models\Value $value
     *
     * @return bool
     */
    protected function trashValue(Value $value)
    {
        if (! is_null($value->getAttribute('content'))) {
            return false;
        }

        if ($value->exists) {
            // Push value to the trash
            $this->trash->push($value);
        }

        return true;
    }
}
