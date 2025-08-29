<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeMenu extends Model
{
    protected $table = 'recipe_menu';

    protected $fillable = [
        'recipe_id',
        'menu_id',
    ];

    /**
     * Relation avec Recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Relation avec Menu
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
