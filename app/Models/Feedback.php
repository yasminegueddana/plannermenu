<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'note',
        'comment',
    ];

    protected $casts = [
        'note' => 'float',
    ];

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
}
