<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributeValueIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('rinvex.attributes.tables.attribute_boolean_values'), function (Blueprint $table) {
            $table->index(['attribute_id', 'entity_id', 'entity_type'], 'attribute_boolean_values_index');
            $table->index(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_datetime_values'), function (Blueprint $table) {
            $table->index(['attribute_id', 'entity_id', 'entity_type'], 'attribute_datetime_values_index');
            $table->index(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_integer_values'), function (Blueprint $table) {
            $table->index(['attribute_id', 'entity_id', 'entity_type'], 'attribute_integer_values_index');
            $table->index(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_text_values'), function (Blueprint $table) {
            $table->index(['attribute_id', 'entity_id', 'entity_type'], 'attribute_text_values_index');
        });
        Schema::table(config('rinvex.attributes.tables.attribute_varchar_values'), function (Blueprint $table) {
            $table->index(['attribute_id', 'entity_id', 'entity_type'], 'attribute_varchar_values_index');
            $table->index(['content']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('rinvex.attributes.tables.attribute_boolean_values'), function (Blueprint $table) {
            $table->dropIndex('attribute_boolean_values_index');
            $table->dropIndex(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_datetime_values'), function (Blueprint $table) {
            $table->dropIndex('attribute_datetime_values_index');
            $table->dropIndex(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_integer_values'), function (Blueprint $table) {
            $table->dropIndex('attribute_integer_values_index');
            $table->dropIndex(['content']);
        });
        Schema::table(config('rinvex.attributes.tables.attribute_text_values'), function (Blueprint $table) {
            $table->dropIndex('attribute_text_values_index');
        });
        Schema::table(config('rinvex.attributes.tables.attribute_varchar_values'), function (Blueprint $table) {
            $table->dropIndex('attribute_varchar_values_index');
            $table->dropIndex(['content']);
        });
    }
}
