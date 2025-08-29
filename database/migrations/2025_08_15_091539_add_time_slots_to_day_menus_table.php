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
            $table->time('start_time')->nullable(); // Heure de début (ex: 08:00)
            $table->time('end_time')->nullable();   // Heure de fin (ex: 12:00)
            $table->string('menu_name')->nullable(); // Nom du menu (ex: "Menu Petit-déjeuner Continental")
            $table->text('description')->nullable(); // Description du menu
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_menus', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'menu_name', 'description']);
        });
    }
};
