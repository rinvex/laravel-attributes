<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.attributes.tables.attributes'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->mediumInteger('sort_order')->unsigned()->default(0);
            $table->string('group')->nullable();
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_collection')->default(false);
            $table->text('default')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.attributes.tables.attributes'));
    }
}
