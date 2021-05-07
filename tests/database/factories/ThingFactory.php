<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;
use Rinvex\Attributes\Tests\Models\Thing;

$factory->define(Thing::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->words(2, true)),
        'code' => $faker->unique()->ean13,
    ];
});

$factory->state(Thing::class, 'eav', function (Faker $faker) {
    $thing = new Thing();
    $eavAttributes = [];
    foreach ($thing->getEntityAttributes() as $entityAttribute) {
        $slug = $entityAttribute->slug;
        // TODO: Define any other types that are used.
        if ($entityAttribute->type === 'bool') {
            $eavAttributes[$slug] = (mt_rand(1, 1000) > 500) ? true : false;
        } else {
            $eavAttributes[$slug] = $faker->word;
        }
    }
    return $eavAttributes;
});
