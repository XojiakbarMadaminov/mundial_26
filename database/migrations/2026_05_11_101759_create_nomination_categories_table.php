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
        Schema::create('nomination_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->enum('type', ['player', 'team', 'number', 'text']);
            $table->unsignedInteger('points')->default(30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tournament_id', 'key']);
            $table->index(['tournament_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomination_categories');
    }
};
