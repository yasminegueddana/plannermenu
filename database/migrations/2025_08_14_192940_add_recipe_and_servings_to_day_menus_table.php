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
        Schema::table('day_menus', function (Blueprint $table) {
            $table->foreignId('recipe_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('servings')->default(1); // Nombre de personnes
            $table->index(['recipe_id', 'servings']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_menus', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->dropColumn(['recipe_id', 'servings']);
        });
    }
};
