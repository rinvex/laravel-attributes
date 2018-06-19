<?php

class AttributeCreationTest extends \TestCase
{
    public function testAttributeCreation()
    {
        $attribute = app('rinvex.attributes.attribute')->create([
            'slug' => 'count',
            'type' => 'integer',
            'name' => 'Count',
            'entities' => ['User'],
        ]);

        $this->assertDatabaseHas('attributes', ['slug' => 'count', 'type' => 'integer']);
        $this->assertDatabaseHas('attribute_entity', ['attribute_id' => $attribute->id, 'entity_type' => 'User']);
    }
}