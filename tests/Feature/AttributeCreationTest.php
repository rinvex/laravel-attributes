<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Feature;

use Rinvex\Attributes\Tests\TestCase;
use Rinvex\Attributes\Tests\Models\User;

class AttributeCreationTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_attribute()
    {
        $attribute = $this->createAttribute();

        $this->assertDatabaseHas('attributes', ['slug' => 'count', 'type' => 'integer']);
        $this->assertDatabaseHas('attribute_entity', ['attribute_id' => $attribute->id, 'entity_type' => User::class]);
    }

    /** @test */
    public function it_ensures_snake_case_slugs()
    {
        $attribute = $this->createAttribute(['name' => 'Foo Bar']);

        $this->assertEquals('foo_bar', $attribute->slug);
        $this->assertDatabaseHas('attributes', ['slug' => 'foo_bar', 'type' => 'integer']);
    }

    /** @test */
    public function it_ensures_snake_case_slugs_even_if_dashed_slugs_provided()
    {
        $attribute = $this->createAttribute(['slug' => 'foo-bar']);

        $this->assertEquals('foo_bar', $attribute->slug);
    }

    /** @test */
    public function it_ensures_unique_slugs()
    {
        $this->createAttribute(['name' => 'foo']);
        $this->createAttribute(['name' => 'foo']);

        $this->assertDatabaseHas('attributes', ['slug' => 'foo_1']);
    }

    /** @test */
    public function it_ensures_unique_slugs_even_if_slugs_explicitly_provided()
    {
        $this->createAttribute(['slug' => 'foo']);
        $this->createAttribute(['slug' => 'foo']);

        $this->assertDatabaseHas('attributes', ['slug' => 'foo_1']);
    }

    /** @test */
    public function it_ensures_attributable_cache_will_clear()
    {
        // Create an attribute.
        $this->createAttribute(['slug' => 'foo']);
        $this->assertDatabaseHas('attributes', ['slug' => 'foo']);

        $user = app()->make(User::class);
        $this->assertEquals(1, $user->getEntityAttributes()->count());

        // Create another attribute.
        $this->createAttribute(['slug' => 'bar']);
        $this->assertDatabaseHas('attributes', ['slug' => 'bar']);
        $this->assertEquals(2, $user->getEntityAttributes()->count());

        // Create three more.
        $this->createAttribute(['slug' => 'baz']);
        $this->createAttribute(['slug' => 'beans']);
        $this->createAttribute(['slug' => 'blorg']);
        $this->assertEquals(5, $user->getEntityAttributes()->count());
    }

    protected function createAttribute($attributes = [])
    {
        return app('rinvex.attributes.attribute')->create(array_merge([
            'type' => 'integer',
            'name' => 'Count',
            'entities' => [User::class],
        ], $attributes));
    }
}
