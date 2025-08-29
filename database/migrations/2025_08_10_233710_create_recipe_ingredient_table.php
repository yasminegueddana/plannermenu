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
        Schema::create('recipe_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->float('quantity'); 
            $table->string('unit'); 
            $table->timestamps();

            $table->unique(['recipe_id', 'ingredient_id']);
            $table->index('recipe_id');
            $table->index('ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredient');
    }
};
