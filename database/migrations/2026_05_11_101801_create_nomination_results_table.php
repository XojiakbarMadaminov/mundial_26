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
        Schema::create('nomination_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nomination_category_id')->constrained()->cascadeOnDelete();
            $table->string('value_text')->nullable();
            $table->integer('value_number')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'nomination_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomination_results');
    }
};
