<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Relation avec User 
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation many-to-many avec Recipe via RecipeMenu
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_menu')
                    ->withTimestamps();
    }

    /**
     * Relation avec DayMenu
     */
    public function dayMenus(): HasMany
    {
        return $this->hasMany(DayMenu::class);
    }

    /**
     * Relation avec ShoppingList
     */
    public function shoppingLists(): HasMany
    {
        return $this->hasMany(ShoppingList::class);
    }
}
