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
        Schema::create('tag_translations', function (Blueprint $table) {
            $table->bigIncrements('id')->index();

            $table->foreignId('tag_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('lang', 8);
            $table->string('value', 255);

            $table->unique(
                ['tag_id', 'lang'],
                'tag_translations_index_unique'
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_translations');
    }
};
