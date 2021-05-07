<?php

declare(strict_types=1);

namespace Rinvex\Attributes\Tests\Feature;

use Rinvex\Attributes\Tests\TestCase;
use Rinvex\Attributes\Tests\Models\Thing;

class AttributeValuesTest extends TestCase
{
    /**
     * Test basic EAV functionality.
     *
     * @return void
     */
    public function test_basic_eav_functionality()
    {
        $this->createAttributes();
        $size = 'small';
        $colour = 'red';
        $featured = true;
        $thing = factory(Thing::class, 1)->create(['code' => 'EAVTEST'])->first();
        $thing->size = $size;
        $thing->colour = $colour;
        $thing->featured = $featured;
        $thing->save();
        // Fetch the thing again and check that the size, colour and featured have been saved.
        $thing = Thing::where('code', 'EAVTEST')->first();
        // Check that the thing exists.
        $this->assertDatabaseHas('things', [
            'code' => 'EAVTEST',
        ]);
        // Check that the size, colour and featured are as expected.
        $this->assertEquals($size, $thing->size);
        $this->assertEquals($colour, $thing->colour);
        $this->assertEquals($featured, $thing->featured);
    }

    /**
     * Create EAV attributes to use in tests.
     *
     * @return void
     */
    protected function createAttributes()
    {
        app('rinvex.attributes.attribute')->create([
            'slug' => 'size',
            'type' => 'varchar',
            'name' => 'Thing Size',
            'entities' => [Thing::class],
        ]);
        app('rinvex.attributes.attribute')->create([
            'slug' => 'colour',
            'type' => 'varchar',
            'name' => 'Thing Colour',
            'entities' => [Thing::class],
        ]);
        app('rinvex.attributes.attribute')->create([
            'slug' => 'featured',
            'type' => 'bool',
            'name' => 'Is Thing Featured',
            'entities' => [Thing::class],
        ]);
        $this->assertDatabaseHas('attributes', [
            'slug' => 'size',
        ]);
        $this->assertDatabaseHas('attributes', [
            'slug' => 'colour',
        ]);
        $this->assertDatabaseHas('attributes', [
            'slug' => 'featured',
        ]);
    }
}
