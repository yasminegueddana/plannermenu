<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayMenu extends Model
{
    protected $fillable = [
        'menu_id',
        'day',
        'meal',
        'user_id',
        'recipe_id',
        'servings',
        'start_time',
        'end_time',
        'menu_name',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relation avec Menu
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Relation avec User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec Recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Calculer les ingrédients avec quantités ajustées selon le nombre de personnes
     */
    public function getAdjustedIngredients()
    {
        if (!$this->recipe) {
            return collect();
        }

        return $this->recipe->ingredients->map(function ($ingredient) {
            $adjustedQuantity = $ingredient->pivot->quantity * $this->servings;

            return [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'original_quantity' => $ingredient->pivot->quantity,
                'adjusted_quantity' => $adjustedQuantity,
                'unit' => $ingredient->pivot->unit,
                'servings' => $this->servings,
            ];
        });
    }

    /**
     * Obtenir le créneau horaire formaté
     */
    public function getTimeSlotAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Vérifier si le menu est actuellement actif (dans le créneau horaire)
     */
    public function isCurrentlyActive()
    {
        if (!$this->start_time || !$this->end_time) {
            return false;
        }

        $now = now()->format('H:i');
        $start = $this->start_time->format('H:i');
        $end = $this->end_time->format('H:i');

        return $now >= $start && $now <= $end;
    }

    /**
     * Scope pour filtrer par créneau horaire
     */
    public function scopeInTimeSlot($query, $startTime, $endTime)
    {
        return $query->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
    }
}
