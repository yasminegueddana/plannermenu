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
        Schema::create('day_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('day'); 
            $table->string('meal'); 
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('menu_id');
            $table->index('user_id');
            $table->index('day');
            $table->index('meal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_menus');
    }
};
