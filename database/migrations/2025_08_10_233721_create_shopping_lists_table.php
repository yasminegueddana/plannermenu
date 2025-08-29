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
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->dateTime('date'); 
            $table->timestamps();

            $table->index('menu_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_lists');
    }
};
