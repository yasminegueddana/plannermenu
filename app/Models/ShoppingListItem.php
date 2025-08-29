<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    protected $fillable = [
        'shopping_list_id',
        'ingredient_name',
        'quantity',
        'unit',
        'recipes',
        'estimated_cost',
        'is_purchased',
    ];

    protected $casts = [
        'recipes' => 'array',
        'quantity' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'is_purchased' => 'boolean',
    ];

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
