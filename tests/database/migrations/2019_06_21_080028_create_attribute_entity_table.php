<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.attributes.tables.attribute_entity'), function (Blueprint $table) {
            // Columns
            $table->integer('attribute_id')->unsigned();
            $table->string('entity_type');
            $table->integer('entity_id')->unsigned()->nullable(); // TODO: Making this nullable for now as it breaks the basic features
            $table->timestamps();

            // Indexes
            $table->unique(['attribute_id', 'entity_type'], 'attributable_attribute_id_entity_type');
            $table->foreign('attribute_id')->references('id')->on(config('rinvex.attributes.tables.attributes'))
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.attributes.tables.attribute_entity'));
    }
}
