<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingList extends Model
{
    protected $fillable = [
        'menu_id',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relation avec Menu
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function items()
    {
        return $this->hasMany(ShoppingListItem::class);
    }
}
