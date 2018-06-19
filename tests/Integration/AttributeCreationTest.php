<?php

class AttributeCreationTest extends \TestCase
{
    public function testBasicAttributeCreation()
    {
        $attribute = $this->createAttribute();

        $this->assertDatabaseHas('attributes', ['slug' => 'count', 'type' => 'integer']);
        $this->assertDatabaseHas('attribute_entity', ['attribute_id' => $attribute->id, 'entity_type' => 'User']);
    }

    public function testSlugShouldBeSnakeCase()
    {
        $attribute = $this->createAttribute(['name' => 'foo-bar']);

        $this->assertEquals('foo_bar', $attribute->slug);
        $this->assertDatabaseHas('attributes', ['slug' => 'foo_bar', 'type' => 'integer']);
    }

    public function testSlugShouldBeUnique()
    {
        $this->createAttribute(['name' => 'foo']);
        $this->createAttribute(['name' => 'foo']);

        $this->assertDatabaseHas('attributes', ['slug' => 'foo_1']);
    }

    public function testSlugShouldBeConvertedToSnakeCaseIfProvided()
    {
        $attribute = $this->createAttribute(['slug' => 'foo-bar']);

        $this->assertEquals('foo_bar', $attribute->slug);
    }

    public function testSlugShouldAlsoBeUniqueWhenProvided()
    {
        $this->createAttribute(['slug' => 'foo']);
        $this->createAttribute(['slug' => 'foo']);

        $this->assertDatabaseHas('attributes', ['slug' => 'foo_1']);
    }

    protected function createAttribute($attributes = [])
    {
        return app('rinvex.attributes.attribute')->create(array_merge([
            'type'     => 'integer',
            'name'     => 'Count',
            'entities' => ['User'],
        ], $attributes));
    }
}