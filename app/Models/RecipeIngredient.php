<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $table = 'recipe_ingredient';

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    /**
     * Relation avec Recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Relation avec Ingredient
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
