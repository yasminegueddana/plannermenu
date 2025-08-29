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
        Schema::create('shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->onDelete('cascade');
            $table->string('ingredient_name');
            $table->decimal('quantity', 8, 2);
            $table->string('unit');
            $table->json('recipes'); // Liste des recettes utilisant cet ingrédient
            $table->decimal('estimated_cost', 8, 2)->default(0);
            $table->boolean('is_purchased')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
    }
};
