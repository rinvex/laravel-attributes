<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Relations;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany as BaseMorphToMany;

class NullableMorphToMany extends BaseMorphToMany
{
    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->where($this->qualifyPivotColumn($this->morphType), $this->morphClass);

        $whereIn = $this->whereInMethod($this->parent, $this->parentKey);
        $this->query->where(function (Builder $query) use ($whereIn, $models) {
            $query->{$whereIn}(
                $this->getQualifiedForeignPivotKeyName(),
                $this->getKeys($models, $this->parentKey)
            )->whereNull($this->qualifyPivotColumn('entity_id'), 'or', false);
        });
    }

    /**
     * Qualify the given column name by the pivot table.
     *
     * @param  string  $column
     * @return string
     */
    public function qualifyPivotColumn($column)
    {
        return Str::contains($column, '.')
            ? $column
            : $this->table.'.'.$column;
    }
}
