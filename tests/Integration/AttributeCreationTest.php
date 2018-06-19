<?php

declare(strict_types=1);
class AttributeCreationTest extends \TestCase
{
    public function testBasicAttributeCreation()
    {
        $attribute = $this->createAttribute();

        $this->assertDatabaseHas('attributes', ['slug' => 'count', 'type' => 'integer']);
        $this->assertDatabaseHas('attribute_entity', ['attribute_id' => $attribute->id, 'entity_type' => 'User']);
    }

    public function testAttributeSlugShouldBeSnakeCase()
    {
        $attribute = $this->createAttribute(['name' => 'foo-bar']);

        $this->assertEquals('foo_bar', $attribute->slug);
        $this->assertDatabaseHas('attributes', ['slug' => 'foo_bar', 'type' => 'integer']);
    }

    // TODO: Add testing for slug check when slug is provided in the creation attributes

    protected function createAttribute($attributes = [])
    {
        return app('rinvex.attributes.attribute')->create(array_merge([
            'type' => 'integer',
            'name' => 'Count',
            'entities' => ['User'],
        ], $attributes));
    }
}
