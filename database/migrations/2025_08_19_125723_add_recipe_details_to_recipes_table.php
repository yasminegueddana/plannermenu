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
        Schema::table('recipes', function (Blueprint $table) {
            $table->longText('instructions')->nullable()->after('description');
            $table->integer('prep_time')->nullable()->comment('Temps de préparation en minutes')->after('instructions');
            $table->integer('cook_time')->nullable()->comment('Temps de cuisson en minutes')->after('prep_time');
            $table->integer('servings')->default(4)->comment('Nombre de portions')->after('cook_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['instructions', 'prep_time', 'cook_time', 'servings']);
        });
    }
};
