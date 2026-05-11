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
        Schema::create('nomination_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nomination_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('value_text')->nullable();
            $table->integer('value_number')->nullable();
            $table->unsignedInteger('points')->default(0);
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'nomination_category_id']);
            $table->index(['tournament_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomination_predictions');
    }
};
