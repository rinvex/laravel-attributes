<?php

declare(strict_types=1);

return [

    // Attributable Database Tables
    'tables' => [

        'attributes' => 'attributes',
        'attribute_entity' => 'attribute_entity',
        'attribute_boolean_values' => 'attribute_boolean_values',
        'attribute_datetime_values' => 'attribute_datetime_values',
        'attribute_integer_values' => 'attribute_integer_values',
        'attribute_text_values' => 'attribute_text_values',
        'attribute_varchar_values' => 'attribute_varchar_values',

    ],

    // Attributable Models
    'models' => [

        'attribute' => \Rinvex\Attributable\Models\Attribute::class,
        'attribute_entity' => \Rinvex\Attributable\Models\AttributeEntity::class,

    ],

];
