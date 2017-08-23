<?php

declare(strict_types=1);

namespace Rinvex\Attributable\Contracts;

/**
 * Rinvex\Attributable\Contracts\AttributeContract.
 *
 * @property int                                                                      $id
 * @property string                                                                   $slug
 * @property array                                                                    $name
 * @property array                                                                    $description
 * @property int                                                                      $sort_order
 * @property string                                                                   $group
 * @property string                                                                   $type
 * @property bool                                                                     $is_required
 * @property bool                                                                     $is_collection
 * @property string                                                                   $default
 * @property \Carbon\Carbon                                                           $created_at
 * @property \Carbon\Carbon                                                           $updated_at
 * @property array                                                                    $entities
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rinvex\Fort\Models\User[] $values
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute ordered($direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereIsCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Attributable\Models\Attribute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
interface AttributeContract
{
    //
}
