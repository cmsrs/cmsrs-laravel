<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('taggable_type');
            $table->unsignedBigInteger('taggable_id');
            $table->string('lang', 8);

            $table->unique(
                ['tag_id', 'taggable_type', 'taggable_id', 'lang'],
                'taggables_index_unique'
            );

            $table->index(
                ['taggable_type', 'taggable_id'],
                'taggables_taggable_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
